<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use AmjadIqbal\FilamentRecordSettings\Pages\RecordSettingsPage;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

/** A page migrated from quadrubo/filament-model-settings keeps its getSettingRecord() method. */
class LegacyPage extends RecordSettingsPage
{
    public static function getSettingRecord(): ?Model
    {
        return Team::query()->first();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('locale')->options(['en' => 'English', 'de' => 'Deutsch']),
        ]);
    }
}
