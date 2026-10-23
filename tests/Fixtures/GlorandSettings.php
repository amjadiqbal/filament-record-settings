<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use Glorand\Model\Settings\Traits\HasSettings;
use Glorand\Model\Settings\Traits\HasSettingsField;

// glorand v8 has one trait per driver; v9 has one trait and a driver setting. The fixtures run on both.
if (trait_exists(HasSettingsField::class)) {
    trait GlorandSettings
    {
        use HasSettingsField;
    }
} else {
    trait GlorandSettings
    {
        use HasSettings;
    }
}
