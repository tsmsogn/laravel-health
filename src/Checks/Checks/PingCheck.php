<?php

namespace Spatie\Health\Checks\Checks;

use Exception;
use Illuminate\Support\Facades\Http;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Exceptions\InvalidCheck;

class PingCheck extends Check
{
    public ?string $url = null;
    public ?string $failureMessage = null;
    public int $timeout = 1;

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function failureMessage(string $failureMessage): self
    {
        $this->failureMessage = $failureMessage;

        return $this;
    }

    public function run(): Result
    {
        if (is_null($this->url)) {
            throw InvalidCheck::urlNotSet();
        }

        try {
            if (! Http::timeout($this->timeout)->get($this->url)->successful()) {
                return $this->failedResult();
            }
        } catch (Exception $exception) {
            return $this->failedResult();
        }

        return Result::make()
            ->ok()
            ->shortSummary('reachable');
    }

    protected function failedResult(): Result
    {
        return Result::make()
            ->failed()
            ->shortSummary('unreachable')
            ->notificationMessage($this->failureMessage ?? "Pinging {$this->getName()} failed.");
    }
}
