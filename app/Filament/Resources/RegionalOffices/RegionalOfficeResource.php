<?php

namespace App\Filament\Resources\RegionalOffices;

use App\Filament\Resources\RegionalOffices\Pages\CreateRegionalOffice;
use App\Filament\Resources\RegionalOffices\Pages\EditRegionalOffice;
use App\Filament\Resources\RegionalOffices\Pages\ListRegionalOffices;
use App\Filament\Resources\RegionalOffices\Schemas\RegionalOfficeForm;
use App\Filament\Resources\RegionalOffices\Tables\RegionalOfficesTable;
use App\Models\RegionalOffice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RegionalOfficeResource extends Resource
{
    protected static ?string $model = RegionalOffice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $recordTitleAttribute = 'region';

    protected static ?string $navigationLabel = 'Региональные представительства';

    protected static ?string $modelLabel = 'представительство';

    protected static ?string $pluralModelLabel = 'Региональные представительства';

    protected static string|UnitEnum|null $navigationGroup = 'Структура';

    protected static ?int $navigationSort = 32;

    public static function form(Schema $schema): Schema
    {
        return RegionalOfficeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegionalOfficesTable::configure($table);
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
            'index' => ListRegionalOffices::route('/'),
            'create' => CreateRegionalOffice::route('/create'),
            'edit' => EditRegionalOffice::route('/{record}/edit'),
        ];
    }
}
