<?php

namespace App\Filament\Resources\MapRegions\Schemas;

use App\Enums\RiskLevel;
use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MapRegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label('Идентификатор (slug)')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Латиница, напр. dushanbe, khatlon.'),
                LocaleTabs::text('name', 'Название'),
                LocaleTabs::text('center', 'Центр'),
                Select::make('risk')
                    ->label('Уровень риска')
                    ->options(RiskLevel::class)
                    ->native(false)
                    ->required()
                    ->default(RiskLevel::Low->value),
                TextInput::make('active_incidents')
                    ->label('Активные инциденты')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                TextInput::make('stations')
                    ->label('Станции')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                LocaleTabs::textarea('note', 'Примечание'),
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
