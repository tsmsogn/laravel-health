<?php

namespace Spatie\Health\ResultStores;

use Illuminate\Support\Collection;
use Spatie\Health\Checks\Result;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResults;

class CacheHealthResultStore implements ResultStore
{
    public string $cacheStore;
    public string $cacheKey;

    public function __construct(
        string $cacheStore = 'file',
        string $cacheKey = 'healthStoreResults'
    ) {
        $this->cacheStore = $cacheStore;
        $this->cacheKey = $cacheKey;
    }

    public function save(Collection $checkResults): void
    {
        $report = new StoredCheckResults(now());

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
            ->each(function (StoredCheckResult $check) use ($report) {
                $report->addCheck($check);
            });

        cache()
            ->store($this->cacheStore)
            ->put($this->cacheKey, $report->toJson());
    }

    public function latestResults(): ?StoredCheckResults
    {
        $healthResultsJson = cache()
            ->store($this->cacheStore)
            ->get($this->cacheKey);

        if (! $healthResultsJson) {
            return null;
        }

        return StoredCheckResults::fromJson($healthResultsJson);
    }
}
