<?php

namespace App\Filament\Resources\Directions\Schemas;

use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DirectionForm
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
                    ->helperText('Латиница, напр. rescue, civil-defense.'),
                TextInput::make('icon')
                    ->label('Иконка (lucide-react)')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Имя иконки lucide-react, напр. LifeBuoy, Flame.'),
                LocaleTabs::text('title', 'Название'),
                LocaleTabs::textarea('description', 'Описание'),
                TextInput::make('stat_value')
                    ->label('Показатель: значение')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Строка, напр. «12 480», «8 мин», «24/7».'),
                LocaleTabs::text('stat_label', 'Показатель: подпись'),
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
