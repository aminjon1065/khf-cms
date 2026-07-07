<?php

namespace App\Filament\Resources\Directions;

use App\Filament\Resources\Directions\Pages\CreateDirection;
use App\Filament\Resources\Directions\Pages\EditDirection;
use App\Filament\Resources\Directions\Pages\ListDirections;
use App\Filament\Resources\Directions\Schemas\DirectionForm;
use App\Filament\Resources\Directions\Tables\DirectionsTable;
use App\Models\Direction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DirectionResource extends Resource
{
    protected static ?string $model = Direction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Направления деятельности';

    protected static ?string $modelLabel = 'направление';

    protected static ?string $pluralModelLabel = 'Направления деятельности';

    protected static string|UnitEnum|null $navigationGroup = 'Деятельность';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return DirectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DirectionsTable::configure($table);
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
            'index' => ListDirections::route('/'),
            'create' => CreateDirection::route('/create'),
            'edit' => EditDirection::route('/{record}/edit'),
        ];
    }
}
