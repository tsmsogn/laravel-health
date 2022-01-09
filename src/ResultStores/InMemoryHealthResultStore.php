<?php

namespace Spatie\Health\ResultStores;

use Illuminate\Support\Collection;
use Spatie\Health\Checks\Result;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResults;

class InMemoryHealthResultStore implements ResultStore
{
    protected static ?StoredCheckResults $storedCheckResults = null;

    public function save(Collection $checkResults): void
    {
        self::$storedCheckResults = new StoredCheckResults(now());

        $checkResults
            ->map(function (Result $result) {
                return new StoredCheckResult(
                    $result->check->getName(),
                    $result->check->getLabel(),
                    $result->getNotificationMessage(),
                    $result->getShortSummary(),
                    (string)$result->status->value,
                    $result->meta,
                );
            })
            ->each(function (StoredCheckResult $check) {
                 if (self::$storedCheckResults)
                     self::$storedCheckResults->addCheck($check);
            });
    }

    public function latestResults(): ?StoredCheckResults
    {
        return self::$storedCheckResults;
    }
}
