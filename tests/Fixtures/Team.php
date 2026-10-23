<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Settings through glorand/laravel-model-settings (field driver).
 *
 * @property int $id
 * @property string $name
 */
class Team extends Model
{
    use GlorandSettings;

    protected $guarded = [];

    /**
     * @var array<string, mixed>
     */
    public $defaultSettings = ['locale' => 'en', 'seats' => 5];

    /**
     * @var array<string, mixed>
     */
    public $settingsRules = ['seats' => 'integer|max:50'];
}
