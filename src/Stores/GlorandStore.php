<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Stores;

use AmjadIqbal\FilamentRecordSettings\Contracts\SettingsStore;
use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Illuminate\Database\Eloquent\Model;

/**
 * Settings kept by glorand/laravel-model-settings (v8 or v9), whatever driver the model uses: field, table, redis or
 * cache. Defaults and `$settingsRules` stay glorand's job.
 */
class GlorandStore implements SettingsStore
{
    public static function supports(Model $record): bool
    {
        return interface_exists(SettingsManagerContract::class)
            && method_exists($record, 'settings')
            && $record->settings() instanceof SettingsManagerContract;
    }

    public function get(Model $record): array
    {
        return $this->manager($record)->all();
    }

    public function put(Model $record, array $values): void
    {
        // Top-level keys only: a nested array from the form replaces that key as a whole, the way the form shows it.
        $this->manager($record)->setMultiple($values);
    }

    protected function manager(Model $record): SettingsManagerContract
    {
        /** @var SettingsManagerContract */
        return $record->settings(); // @phpstan-ignore method.notFound
    }
}
