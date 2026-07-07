<?php

namespace App\Filament\Resources\ContactOffices;

use App\Filament\Resources\ContactOffices\Pages\CreateContactOffice;
use App\Filament\Resources\ContactOffices\Pages\EditContactOffice;
use App\Filament\Resources\ContactOffices\Pages\ListContactOffices;
use App\Filament\Resources\ContactOffices\Schemas\ContactOfficeForm;
use App\Filament\Resources\ContactOffices\Tables\ContactOfficesTable;
use App\Models\ContactOffice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContactOfficeResource extends Resource
{
    protected static ?string $model = ContactOffice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'region';

    protected static ?string $navigationLabel = 'Офисы и приёмные';

    protected static ?string $modelLabel = 'офис';

    protected static ?string $pluralModelLabel = 'Офисы и приёмные';

    protected static string|UnitEnum|null $navigationGroup = 'Контакты';

    protected static ?int $navigationSort = 41;

    public static function form(Schema $schema): Schema
    {
        return ContactOfficeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactOfficesTable::configure($table);
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
            'index' => ListContactOffices::route('/'),
            'create' => CreateContactOffice::route('/create'),
            'edit' => EditContactOffice::route('/{record}/edit'),
        ];
    }
}
