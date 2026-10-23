<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Settings in the `prefs` JSON column, edited from a resource.
 *
 * @property int $id
 * @property string $name
 * @property array<string, mixed> | null $prefs
 */
class Project extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['prefs' => 'array'];
    }
}
