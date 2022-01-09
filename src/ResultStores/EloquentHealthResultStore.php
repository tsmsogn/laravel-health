<?php

namespace Spatie\Health\ResultStores;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Health\Checks\Result;
use Spatie\Health\Exceptions\CouldNotSaveResultsInStore;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResults;

class EloquentHealthResultStore implements ResultStore
{
    public static function determineHistoryItemModel(): string
    {
        $defaultHistoryClass = HealthCheckResultHistoryItem::class;
        $eloquentResultStore = EloquentHealthResultStore::class;

        $historyItemModel = config("health.result_stores.{$eloquentResultStore}.model", $defaultHistoryClass);

        if (! is_a($historyItemModel, $defaultHistoryClass, true)) {
            throw CouldNotSaveResultsInStore::doesNotExtendHealthCheckResultHistoryItem($historyItemModel);
        }

        return $historyItemModel;
    }

    /** @return HealthCheckResultHistoryItem|object */
    public static function getHistoryItemInstance()
    {
        $historyItemClassName = static::determineHistoryItemModel();

        return new $historyItemClassName();
    }

    /** @param Collection<int, Result> $checkResults */
    public function save(Collection $checkResults): void
    {
        $batch = Str::uuid();
        $checkResults->each(function (Result $result) use ($batch) {
            (static::determineHistoryItemModel())::create([
                'check_name' => $result->check->getName(),
                'check_label' => $result->check->getLabel(),
                'status' => $result->status,
                'notification_message' => $result->getNotificationMessage(),
                'short_summary' => $result->getShortSummary(),
                'meta' => $result->meta,
                'batch' => $batch,
                'ended_at' => $result->ended_at,
            ]);
        });
    }

    public function latestResults(): ?StoredCheckResults
    {
        if (! $latestItem = (static::determineHistoryItemModel())::latest()->first()) {
            return null;
        }

        $storedCheckResults = (static::determineHistoryItemModel())::query()
            ->where('batch', $latestItem->batch)
            ->get()
            ->map(function (HealthCheckResultHistoryItem $historyItem) {
                return new StoredCheckResult(
                    $historyItem->check_name,
                    $historyItem->check_label,
                    $historyItem->notification_message,
                    $historyItem->short_summary,
                    $historyItem->status,
                    $historyItem->meta,
                );
            });

        return new StoredCheckResults(
            $latestItem->created_at,
            $storedCheckResults,
        );
    }
}
