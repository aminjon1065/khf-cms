<?php

namespace App\Filament\Resources\Hotlines\Schemas;

use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HotlineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Номер')
                    ->required()
                    ->maxLength(255),
                LocaleTabs::text('label', 'Название'),
                LocaleTabs::textarea('note', 'Примечание'),
                Toggle::make('is_primary')
                    ->label('Главная линия')
                    ->default(false),
                TextInput::make('sort')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('active')
                    ->label('Активно')
                    ->default(true),
            ]);
    }
}
