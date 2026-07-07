<?php

namespace App\Filament\Resources\MapRegions;

use App\Filament\Resources\MapRegions\Pages\CreateMapRegion;
use App\Filament\Resources\MapRegions\Pages\EditMapRegion;
use App\Filament\Resources\MapRegions\Pages\ListMapRegions;
use App\Filament\Resources\MapRegions\Schemas\MapRegionForm;
use App\Filament\Resources\MapRegions\Tables\MapRegionsTable;
use App\Models\MapRegion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MapRegionResource extends Resource
{
    protected static ?string $model = MapRegion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Регионы (карта)';

    protected static ?string $modelLabel = 'регион карты';

    protected static ?string $pluralModelLabel = 'Регионы (карта)';

    protected static string|UnitEnum|null $navigationGroup = 'Карта';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return MapRegionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MapRegionsTable::configure($table);
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
            'index' => ListMapRegions::route('/'),
            'create' => CreateMapRegion::route('/create'),
            'edit' => EditMapRegion::route('/{record}/edit'),
        ];
    }
}
