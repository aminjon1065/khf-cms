<?php

namespace App\Filament\Resources\ContactOffices\Schemas;

use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContactOfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                LocaleTabs::text('region', 'Регион / офис'),
                LocaleTabs::textarea('address', 'Адрес'),
                LocaleTabs::text('hours', 'Часы работы'),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Эл. почта')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_head')
                    ->label('Центральный аппарат (головной офис)')
                    ->helperText('Только один офис может быть головным — включение снимет отметку с остальных.')
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
