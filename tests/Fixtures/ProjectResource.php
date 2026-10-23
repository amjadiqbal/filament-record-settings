<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use AmjadIqbal\FilamentRecordSettings\Actions\EditRecordSettingsAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            Toggle::make('prefs.public')->recordSetting(),
            Select::make('prefs.visibility')->options(['team' => 'Team', 'org' => 'Organisation'])->recordSetting(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
            ])
            ->recordActions([
                EditRecordSettingsAction::make()
                    ->settingsColumn('prefs')
                    ->settingsDefaults(['color' => 'teal'])
                    ->schema([
                        TextInput::make('color'),
                        Toggle::make('archived_view'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
