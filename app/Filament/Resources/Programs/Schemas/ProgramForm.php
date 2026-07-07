<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Enums\ProgramStatus;
use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                LocaleTabs::text('title', 'Название'),
                TextInput::make('period')
                    ->label('Период')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Напр. 2024–2028.'),
                Select::make('status')
                    ->label('Статус')
                    ->options(ProgramStatus::class)
                    ->native(false)
                    ->required()
                    ->default(ProgramStatus::Active->value),
                LocaleTabs::textarea('description', 'Описание'),
                TextInput::make('sort')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('active')
                    ->label('Активна')
                    ->default(true),
            ]);
    }
}
