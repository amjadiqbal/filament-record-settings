<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use AmjadIqbal\FilamentRecordSettings\Pages\RecordSettingsPage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

/** A team's settings through glorand. */
class TeamSettings extends RecordSettingsPage
{
    public static function getSettingsRecord(): ?Model
    {
        return Team::query()->first();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('locale')->options(['en' => 'English', 'de' => 'Deutsch']),
            TextInput::make('seats')->numeric(),
        ]);
    }
}
