<?php

namespace App\Filament\Resources\Leaders;

use App\Filament\Resources\Leaders\Pages\CreateLeader;
use App\Filament\Resources\Leaders\Pages\EditLeader;
use App\Filament\Resources\Leaders\Pages\ListLeaders;
use App\Filament\Resources\Leaders\Schemas\LeaderForm;
use App\Filament\Resources\Leaders\Tables\LeadersTable;
use App\Models\Leader;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LeaderResource extends Resource
{
    protected static ?string $model = Leader::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Руководство';

    protected static ?string $modelLabel = 'руководитель';

    protected static ?string $pluralModelLabel = 'Руководство';

    protected static string|UnitEnum|null $navigationGroup = 'Структура';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return LeaderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeaders::route('/'),
            'create' => CreateLeader::route('/create'),
            'edit' => EditLeader::route('/{record}/edit'),
        ];
    }
}
