<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Settings through glorand's table driver: no settings column on `orgs`.
 *
 * @property int $id
 * @property string $name
 */
class Org extends Model
{
    use GlorandTableSettings;

    protected $guarded = [];

    protected $settingsDriver = 'table';
}
