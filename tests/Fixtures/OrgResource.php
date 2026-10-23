<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrgResource extends Resource
{
    protected static ?string $model = Org::class;

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
            'index' => ListOrgs::route('/'),
            'edit' => EditOrg::route('/{record}/edit'),
        ];
    }
}
