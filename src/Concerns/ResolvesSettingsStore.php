<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Concerns;

use AmjadIqbal\FilamentRecordSettings\Contracts\SettingsStore;
use AmjadIqbal\FilamentRecordSettings\RecordSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * @internal
 */
trait ResolvesSettingsStore
{
    /**
     * Write settings and report a glorand `$settingsRules` failure against the form field that holds the value.
     *
     * @param  array<string, mixed>  $values
     */
    protected static function writeSettings(SettingsStore $store, Model $record, array $values, string $statePath): void
    {
        try {
            $store->put($record, $values);
        } catch (ValidationException $exception) {
            $messages = [];

            foreach ($exception->errors() as $key => $errors) {
                $messages[ltrim("{$statePath}.{$key}", '.')] = $errors;
            }

            throw ValidationException::withMessages($messages);
        }
    }

    /**
     * @param  array<string, mixed>  $defaults
     */
    protected static function resolveSettingsStore(Model $record, ?string $column, array $defaults = []): SettingsStore
    {
        return RecordSettings::store($record, $column, $defaults);
    }
}
