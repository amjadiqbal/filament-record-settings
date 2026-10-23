<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings;

use AmjadIqbal\FilamentRecordSettings\Commands\MakeRecordSettingsPageCommand;
use Filament\Forms\Components\Field;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentRecordSettingsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-record-settings';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasTranslations()
            ->hasCommand(MakeRecordSettingsPageCommand::class);
    }

    public function packageBooted(): void
    {
        Field::macro('recordSetting', function (?string $prefix = null): Field {
            return RecordSettingField::configure($this, $prefix);
        });

        // The macro name quadrubo/filament-model-settings used, so migrated forms keep working. Not registered when
        // another package already owns it.
        if (! Field::hasMacro('isModelSetting')) {
            Field::macro('isModelSetting', function (?string $prefix = null): Field {
                return RecordSettingField::configure($this, $prefix);
            });
        }
    }
}
