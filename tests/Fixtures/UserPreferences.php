<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use AmjadIqbal\FilamentRecordSettings\Pages\RecordSettingsPage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/** The signed-in user's preferences, in the `settings` column. */
class UserPreferences extends RecordSettingsPage
{
    protected static ?string $title = 'Preferences';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('theme')->options(['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'])->required(),
            Toggle::make('email_digest'),
        ]);
    }
}
