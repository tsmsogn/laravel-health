<?php

namespace Spatie\Health\Checks;

use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Spatie\Health\Enums\Status;
use function trans;

class Result
{
    /** @var array<string, string|int|bool> */
    public array $meta = [];
    public Check $check;
    public ?CarbonInterface $ended_at;
    public Status $status;
    public string $notificationMessage = '';
    public string $shortSummary = '';

    public static function make(string $message = ''): self
    {
        return new self(Status::ok(), $message);
    }

    public function __construct(
        Status  $status,
        string $notificationMessage = '',
        string $shortSummary = ''
    ) {
        $this->shortSummary = $shortSummary;
        $this->notificationMessage = $notificationMessage;
        $this->status = $status;
    }

    public function shortSummary(string $shortSummary): self
    {
        $this->shortSummary = $shortSummary;

        return $this;
    }

    public function getShortSummary(): string
    {
        if (! empty($this->shortSummary)) {
            return $this->shortSummary;
        }

        return Str::of($this->status)->snake()->replace('_', ' ')->title();
    }

    public function check(Check $check): self
    {
        $this->check = $check;

        return $this;
    }

    public function notificationMessage(string $notificationMessage): self
    {
        $this->notificationMessage = $notificationMessage;

        return $this;
    }

    public function getNotificationMessage(): string
    {
        return trans($this->notificationMessage, $this->meta);
    }

    public function ok(string $message = ''): self
    {
        $this->notificationMessage = $message;

        $this->status = Status::ok();

        return $this;
    }

    public function warning(string $message = ''): self
    {
        $this->notificationMessage = $message;

        $this->status = Status::warning();

        return $this;
    }

    public function failed(string $message = ''): self
    {
        $this->notificationMessage = $message;

        $this->status = Status::failed();

        return $this;
    }

    /** @param array<string, mixed> $meta */
    public function meta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function endedAt(CarbonInterface $carbon): self
    {
        $this->ended_at = $carbon;

        return $this;
    }
}
