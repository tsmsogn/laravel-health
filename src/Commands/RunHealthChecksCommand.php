<?php

namespace Spatie\Health\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Enums\Status;
use Spatie\Health\Events\CheckEndedEvent;
use Spatie\Health\Events\CheckStartingEvent;
use Spatie\Health\Exceptions\CheckDidNotComplete;
use Spatie\Health\Health;
use Spatie\Health\Notifications\CheckFailedNotification;
use Spatie\Health\Notifications\Notifiable;
use Spatie\Health\ResultStores\ResultStore;

class RunHealthChecksCommand extends Command
{
    public $signature = 'health:check {--do-not-store-results} {--no-notification} {--fail-command-on-failing-check}';

    public $description = 'Run all health checks';

    /** @var array<int, Exception> */
    protected array $thrownExceptions = [];

    public function handle(): int
    {
        $this->info('Running checks...');

        $results = $this->runChecks();

        if (! $this->option('do-not-store-results')) {
            $this->storeResults($results);
        }

        if (! $this->option('no-notification')) {
            $this->sendNotification($results);
        }

        $this->line('');
        $this->info('All done!');

        return $this->determineCommandResult($results);
    }

    public function runCheck(Check $check): Result
    {
        event(new CheckStartingEvent($check));

        try {
            $this->line('');
            $this->line("Running check: {$check->getLabel()}...");
            $result = $check->run();
        } catch (Exception $exception) {
            $exception = CheckDidNotComplete::make($check, $exception);
            report($exception);

            $this->thrownExceptions[] = $exception;

            $result = $check->markAsCrashed();
        }

        $result
            ->check($check)
            ->endedAt(now());

        $this->outputResult($result, $exception ?? null);

        event(new CheckEndedEvent($check, $result));

        return $result;
    }

    /** @return Collection<int, Result> */
    protected function runChecks(): Collection
    {
        return app(Health::class)
            ->registeredChecks()
            ->map(function (Check $check): Result {
                return $check->shouldRun()
                    ? $this->runCheck($check)
                    : (new Result(Status::skipped()))->check($check);
            });
    }

    /** @param Collection<int, Result> $results */
    protected function storeResults(Collection $results): self
    {
        app(Health::class)
            ->resultStores()
            ->each(fn (ResultStore $store) => $store->save($results));

        return $this;
    }

    protected function sendNotification(Collection $results): self
    {
        $resultsWithMessages = $results->filter(fn (Result $result) => ! empty($result->getNotificationMessage()));

        if ($resultsWithMessages->count() === 0) {
            return $this;
        }

        $notifiableClass = config('health.notifications.notifiable');

        /** @var Notifiable $notifiable */

        $notifiable = app($notifiableClass);

        /** @var array<int, Result> $results */
        $results = $resultsWithMessages->toArray();

        $notification = (new CheckFailedNotification($results));

        $notifiable->notify($notification);

        return $this;
    }

    protected function outputResult(Result $result, ?Exception $exception = null): void
    {
        $status = ucfirst((string)$result->status->value);

        $okMessage = $status;

        if (! empty($result->shortSummary)) {
            $okMessage .= ": {$result->shortSummary}";
        }

        switch ($result->status) {
            case Status::ok(): $this->info($okMessage); break;
            case Status::warning(): $this->comment("{$status}: {$result->getNotificationMessage()}"); break;
            case Status::failed(): $this->error("{$status}: {$result->getNotificationMessage()}"); break;
            case Status::crashed(): $this->error("{$status}}: `" . ($exception ? $exception->getMessage() : '') . "`"); break;
        }
    }

    protected function determineCommandResult(Collection $results): int
    {
        if (! $this->option('fail-command-on-failing-check')) {
            return self::SUCCESS;
        }

        if (count($this->thrownExceptions)) {
            return self::FAILURE;
        }

        $containsFailingCheck = $results->contains(function (Result $result) {
            return in_array($result->status, [
                Status::crashed(),
                Status::failed(),
                Status::warning(),
            ]);
        });

        return $containsFailingCheck
            ? self::FAILURE
            : self::SUCCESS;
    }
}
