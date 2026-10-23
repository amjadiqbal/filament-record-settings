<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings;

use AmjadIqbal\FilamentRecordSettings\Contracts\SettingsStore;
use AmjadIqbal\FilamentRecordSettings\Stores\ColumnStore;
use AmjadIqbal\FilamentRecordSettings\Stores\GlorandStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class RecordSettings
{
    /**
     * The store for a record. With a column, settings live in that JSON column. Without one, a model that uses
     * glorand/laravel-model-settings goes through glorand, and any other model uses its `settings` column.
     *
     * @param  array<string, mixed>  $defaults  used by the column store only; glorand keeps its own defaults
     */
    public static function store(Model $record, ?string $column = null, array $defaults = []): SettingsStore
    {
        if (blank($column) && GlorandStore::supports($record)) {
            return new GlorandStore;
        }

        return new ColumnStore($column ?: 'settings', $defaults);
    }

    /**
     * May the current user change this record's settings? When the record's policy defines the ability
     * (`updateSettings` by default), the policy decides. Without such a policy method, access is the caller's
     * job: the page's `canAccess()`, the action's `visible()`, or the resource's own policy.
     */
    public static function can(Model $record, ?string $ability = 'updateSettings'): bool
    {
        if (blank($ability)) {
            return true;
        }

        $policy = Gate::getPolicyFor($record);

        if ($policy === null || ! method_exists($policy, $ability)) {
            return true;
        }

        return Gate::allows($ability, $record);
    }
}
