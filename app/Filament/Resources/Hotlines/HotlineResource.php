<?php

namespace App\Filament\Resources\Hotlines;

use App\Filament\Resources\Hotlines\Pages\CreateHotline;
use App\Filament\Resources\Hotlines\Pages\EditHotline;
use App\Filament\Resources\Hotlines\Pages\ListHotlines;
use App\Filament\Resources\Hotlines\Schemas\HotlineForm;
use App\Filament\Resources\Hotlines\Tables\HotlinesTable;
use App\Models\Hotline;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HotlineResource extends Resource
{
    protected static ?string $model = Hotline::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $navigationLabel = 'Горячие линии';

    protected static ?string $modelLabel = 'горячая линия';

    protected static ?string $pluralModelLabel = 'Горячие линии';

    protected static string|UnitEnum|null $navigationGroup = 'Контакты';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return HotlineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotlinesTable::configure($table);
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
            'index' => ListHotlines::route('/'),
            'create' => CreateHotline::route('/create'),
            'edit' => EditHotline::route('/{record}/edit'),
        ];
    }
}
