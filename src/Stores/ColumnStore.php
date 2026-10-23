<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Stores;

use AmjadIqbal\FilamentRecordSettings\Contracts\SettingsStore;
use Illuminate\Database\Eloquent\Model;

/**
 * Settings kept as a JSON object in one column of the record's own table. The column may be cast to `array`,
 * `json`, `collection` or `AsArrayObject`, or not cast at all (a JSON string). Defaults come from the model's
 * `getDefaultSettings()` or `$defaultSettings` when it has one, then from `$defaults`.
 */
class ColumnStore implements SettingsStore
{
    /**
     * @param  array<string, mixed>  $defaults
     */
    public function __construct(
        protected string $column = 'settings',
        protected array $defaults = [],
    ) {}

    public function getColumn(): string
    {
        return $this->column;
    }

    public function get(Model $record): array
    {
        return array_replace_recursive($this->defaults, $this->modelDefaults($record), $this->stored($record));
    }

    public function put(Model $record, array $values): void
    {
        $settings = [...$this->stored($record), ...$values];

        $record->setAttribute($this->column, $record->hasCast($this->column) ? $settings : json_encode($settings, JSON_THROW_ON_ERROR));
        $record->save();
    }

    /**
     * @return array<string, mixed>
     */
    protected function stored(Model $record): array
    {
        $value = $record->getAttribute($this->column);

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if ($value instanceof \ArrayObject) {
            $value = $value->getArrayCopy();
        } elseif (is_object($value) && method_exists($value, 'toArray')) {
            $value = $value->toArray();
        } elseif (is_object($value)) {
            $value = (array) $value;
        }

        return is_array($value) ? $value : [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function modelDefaults(Model $record): array
    {
        if (method_exists($record, 'getDefaultSettings')) {
            return (array) $record->getDefaultSettings();
        }

        if (property_exists($record, 'defaultSettings') && is_array($defaults = (fn () => $this->defaultSettings)->call($record))) {
            return $defaults;
        }

        return [];
    }
}
