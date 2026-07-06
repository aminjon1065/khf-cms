<?php

namespace App\Filament\Resources\Regions\Schemas;

use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                LocaleTabs::text('name', 'Название'),
                TextInput::make('sort')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
