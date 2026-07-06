<?php

namespace App\Filament\Resources\Leaders\Schemas;

use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeaderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                LocaleTabs::text('name', 'Имя'),
                LocaleTabs::text('role', 'Должность'),
                LocaleTabs::text('rank', 'Звание', requiredDefault: false),
                LocaleTabs::textarea('bio', 'Биография'),
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
