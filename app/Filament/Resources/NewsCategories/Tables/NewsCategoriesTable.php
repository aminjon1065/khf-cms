<?php

namespace App\Filament\Resources\NewsCategories\Tables;

use App\Filament\Support\LocaleTabs;
use App\Models\NewsCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NewsCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Название'),
                TextColumn::make('translations')
                    ->label('Переводы')
                    ->badge()
                    ->state(fn (NewsCategory $record): string => collect(array_keys(LocaleTabs::LOCALES))
                        ->map(fn (string $locale): string => filled($record->getTranslationWithoutFallback('label', $locale))
                            ? mb_strtoupper($locale)
                            : '—')
                        ->implode(' · ')),
                TextColumn::make('color')
                    ->label('Цвет')
                    ->badge(),
                TextColumn::make('sort')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Активна')
                    ->boolean(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->filters([
                TernaryFilter::make('active')
                    ->label('Активна'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
