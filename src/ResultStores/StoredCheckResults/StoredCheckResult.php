<?php

namespace Spatie\Health\ResultStores\StoredCheckResults;

class StoredCheckResult
{
    public string $name;
    public string $label = '';
    public string $shortSummary = '';
    public string $notificationMessage = '';
    public string $status = '';
    public array $meta = [];

    /**
     * @param string $name
     * @param string $label
     * @param string $notificationMessage
     * @param string $shortSummary
     * @param string $status
     * @param array<string, mixed> $meta
     *
     * @return self
     */
    public static function make(
        string $name,
        string $label = '',
        string $notificationMessage = '',
        string $shortSummary = '',
        string $status = '',
        array  $meta = []
    ): self {
        return new self(...func_get_args());
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $notificationMessage
     * @param string $shortSummary
     * @param string $status
     * @param array<string, mixed> $meta
     */
    public function __construct(
        string $name,
        string $label = '',
        string $notificationMessage = '',
        string $shortSummary = '',
        string $status = '',
        array  $meta = []
    ) {
        $this->meta = $meta;
        $this->status = $status;
        $this->notificationMessage = $notificationMessage;
        $this->shortSummary = $shortSummary;
        $this->label = $label;
        $this->name = $name;
    }

    public function notificationMessage(string $message): self
    {
        $this->notificationMessage = $message;

        return $this;
    }

    public function status(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function shortSummary(string $shortSummary): self
    {
        $this->shortSummary = $shortSummary;

        return $this;
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return $this
     */
    public function meta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'notificationMessage' => $this->notificationMessage,
            'shortSummary' => $this->shortSummary,
            'status' => $this->status,
            'meta' => $this->meta,
        ];
    }
}
