<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            Select::make('settings.locale')->options(['en' => 'English', 'de' => 'Deutsch'])->recordSetting(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeams::route('/'),
            'edit' => EditTeam::route('/{record}/edit'),
        ];
    }
}
