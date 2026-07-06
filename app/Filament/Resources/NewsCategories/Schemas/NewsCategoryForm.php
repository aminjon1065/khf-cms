<?php

namespace App\Filament\Resources\NewsCategories\Schemas;

use App\Enums\CategoryColor;
use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NewsCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                LocaleTabs::text('label', 'Название'),
                Select::make('color')
                    ->label('Цвет')
                    ->options(CategoryColor::class)
                    ->default(CategoryColor::Brand->value)
                    ->required(),
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
