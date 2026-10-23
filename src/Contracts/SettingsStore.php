<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SettingsStore
{
    /**
     * Every setting of the record, with defaults filled in.
     *
     * @return array<string, mixed>
     */
    public function get(Model $record): array;

    /**
     * Write the given top-level keys. Keys that are not given keep their stored value.
     *
     * @param  array<string, mixed>  $values
     */
    public function put(Model $record, array $values): void;
}
