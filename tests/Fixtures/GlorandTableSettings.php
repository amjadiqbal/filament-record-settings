<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use Glorand\Model\Settings\Traits\HasSettings;
use Glorand\Model\Settings\Traits\HasSettingsTable;

// glorand's table driver: the HasSettingsTable trait on v8, HasSettings plus `$settingsDriver = 'table'` on v9.
if (trait_exists(HasSettingsTable::class)) {
    trait GlorandTableSettings
    {
        use HasSettingsTable;
    }
} else {
    trait GlorandTableSettings
    {
        use HasSettings;
    }
}
