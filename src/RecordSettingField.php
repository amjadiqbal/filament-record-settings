<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings;

use AmjadIqbal\FilamentRecordSettings\Stores\ColumnStore;
use AmjadIqbal\FilamentRecordSettings\Stores\GlorandStore;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use WeakMap;

/**
 * The `recordSetting()` field macro: a field in a resource form whose value is a setting of the record.
 *
 *     Toggle::make('settings.email_digest')->recordSetting()
 *     ColorPicker::make('prefs.accent')->recordSetting('prefs')     // JSON column `prefs`
 *
 * The first segment of the field name is the prefix (the settings column for the column store). The field is not
 * saved with the record's attributes; its value is written through the settings store, so it also works on create
 * and keeps the settings the form does not show.
 *
 * @internal
 */
class RecordSettingField
{
    /**
     * Setting values written in this request, per record and prefix. See `guard()`.
     *
     * @var WeakMap<Model, array<string, array{column: bool, values: array<string, mixed>}>> | null
     */
    protected static ?WeakMap $pending = null;

    /**
     * @var WeakMap<object, array<class-string, true>> | null
     */
    protected static ?WeakMap $listening = null;

    /**
     * True while the settings store itself saves the record: its own write is the value to keep.
     */
    protected static bool $writing = false;

    public static function configure(Field $field, ?string $prefix = null): Field
    {
        $prefix ??= Str::before($field->getName(), '.');
        $key = Str::after($field->getName(), $prefix.'.');

        return $field
            ->afterStateHydrated(function (Field $component, ?Model $record) use ($prefix, $key): void {
                if ($record === null || ! $record->exists) {
                    return;
                }

                $settings = static::store($record, $prefix)->get($record);

                if (Arr::has($settings, $key)) {
                    $component->state(Arr::get($settings, $key));
                }
            })
            ->dehydrated(false)
            ->saveRelationshipsUsing(function (Field $component, ?Model $record) use ($prefix, $key): void {
                if ($record === null) {
                    return;
                }

                $store = static::store($record, $prefix);
                [$top, $value] = static::topLevelValue($store, $record, $key, $component->getState());

                static::guard($record, $prefix, $top, $value, $store instanceof ColumnStore);

                static::$writing = true;

                try {
                    $store->put($record, [$top => $value]);
                } catch (ValidationException $exception) {
                    // A glorand `$settingsRules` failure belongs under this field.
                    throw ValidationException::withMessages([
                        $component->getStatePath() => Arr::flatten($exception->errors()),
                    ]);
                } finally {
                    static::$writing = false;
                }
            });
    }

    /**
     * A nested field name (`settings.notify.email`) writes inside its top-level key, keeping that key's other values,
     * the same way in the JSON column and in glorand.
     *
     * @return array{0: string, 1: mixed}
     */
    protected static function topLevelValue(Contracts\SettingsStore $store, Model $record, string $key, mixed $state): array
    {
        if (! str_contains($key, '.')) {
            return [$key, $state];
        }

        $top = Str::before($key, '.');
        $value = $record->exists ? ($store->get($record)[$top] ?? []) : [];
        $value = is_array($value) ? $value : [];

        Arr::set($value, Str::after($key, '.'), $state);

        return [$top, $value];
    }

    protected static function store(Model $record, string $prefix): Contracts\SettingsStore
    {
        // A glorand model keeps settings in its own store; the prefix then only names the form group.
        return RecordSettings::store($record, GlorandStore::supports($record) ? null : $prefix);
    }

    /**
     * Filament leaves the prefix key in the form data (`prefs => []` once its setting fields are taken out), and
     * an edit page saves that data after the settings were written. Before any save of this record in this
     * request, put back what the settings store holds, so that empty key does not wipe the settings.
     */
    protected static function guard(Model $record, string $prefix, string $key, mixed $value, bool $isColumn): void
    {
        static::$pending ??= new WeakMap;
        static::$listening ??= new WeakMap;

        $entries = static::$pending[$record] ?? [];
        $entries[$prefix]['column'] = $isColumn;
        $entries[$prefix]['values'][$key] = $value;
        static::$pending[$record] = $entries;

        $dispatcher = Model::getEventDispatcher();

        if ($dispatcher === null || isset(static::$listening[$dispatcher][$record::class])) {
            return;
        }

        $classes = static::$listening[$dispatcher] ?? [];
        $classes[$record::class] = true;
        static::$listening[$dispatcher] = $classes;

        $record::saving(fn (Model $model) => static::restore($model));
    }

    protected static function restore(Model $model): void
    {
        if (static::$writing) {
            return;
        }

        foreach (static::$pending[$model] ?? [] as $prefix => $entry) {
            $attributes = $model->getAttributes();

            if (! array_key_exists($prefix, $attributes)) {
                continue;
            }

            $original = $model->getRawOriginal();

            if (! array_key_exists($prefix, $original)) {
                // Not a column (glorand's table, redis or cache driver): drop the key the form added.
                unset($attributes[$prefix]);
                $model->setRawAttributes($attributes);

                continue;
            }

            if (! $entry['column']) {
                // glorand's field driver column: glorand has written it already; undo the form's overwrite.
                $attributes[$prefix] = $original[$prefix];
                $model->setRawAttributes($attributes);

                continue;
            }

            // Stored keys, then keys other (core) fields of the form set, then this request's setting values.
            $merged = [...static::decode($original[$prefix]), ...static::decode($attributes[$prefix]), ...$entry['values']];

            $model->setAttribute($prefix, $model->hasCast($prefix) ? $merged : json_encode($merged, JSON_THROW_ON_ERROR));
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected static function decode(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? $value : [];
    }
}
