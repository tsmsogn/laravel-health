<?php

namespace Spatie\Health\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Spatie\Health\Enums\Status;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;

class StatusIndicator extends Component
{
    public StoredCheckResult $result;

    public function __construct(StoredCheckResult $result)
    {
        $this->result = $result;
    }

    public function render(): View
    {
        return view('health::status-indicator', [
            'result' => $this->result,
            'backgroundColor' => fn (string $status) => $this->getBackgroundColor($status),
            'iconColor' => fn (string $status) => $this->getIconColor($status),
            'icon' => fn (string $status) => $this->getIcon($status),
        ]);
    }

    protected function getBackgroundColor(string $status): string
    {
        switch ($status) {
            case Status::ok()->value:
                return 'md:bg-emerald-100 md:dark:bg-emerald-800';
            case Status::warning()->value:
                return 'md:bg-yellow-100  md:dark:bg-yellow-800';
            case Status::skipped()->value:
                return 'md:bg-blue-100  md:dark:bg-blue-800';
            case Status::failed()->value:
            case Status::crashed()->value:
                return 'md:bg-red-100  md:dark:bg-red-800';
        }

        return 'md:bg-gray-100 md:dark:bg-gray-600';
    }

    protected function getIconColor(string $status): string
    {
        switch ($status) {
            case Status::ok()->value:
                return 'text-emerald-500';
            case Status::warning()->value:
                return 'text-yellow-500';
            case Status::skipped()->value:
                return 'text-blue-500';
            case Status::failed()->value:
            case Status::crashed()->value:
                return 'text-red-500';
        }

        return 'text-gray-500';
    }

    protected function getIcon(string $status): string
    {
        switch ($status) {
            case Status::ok()->value:
                return 'check-circle';
            case Status::warning()->value:
                return 'exclamation-circle';
            case Status::skipped()->value:
                return 'arrow-circle-right';
            case Status::failed()->value:
            case Status::crashed()->value:
                return 'x-circle';
        }

        return '';
    }
}
