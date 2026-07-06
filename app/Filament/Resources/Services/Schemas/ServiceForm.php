<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Кнопка')
                    ->schema([
                        TextInput::make('key')
                            ->label('Ключ (id)')
                            ->helperText('Стабильный идентификатор для фронта: 112, report, …')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        LocaleTabs::text('title', 'Заголовок'),
                        LocaleTabs::text('subtitle', 'Подпись'),
                    ]),
                Section::make('Действие')
                    ->columns(2)
                    ->schema([
                        TextInput::make('tel')
                            ->label('Телефон (tel:)')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('route')
                            ->label('Маршрут фронта')
                            ->helperText('Ключ маршрута: report | safety | subscribe | …')
                            ->maxLength(50),
                        Toggle::make('primary')
                            ->label('Основная (акцент)'),
                        Toggle::make('active')
                            ->label('Активна')
                            ->default(true),
                    ]),
            ]);
    }
}
