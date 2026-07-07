<?php

namespace App\Filament\Resources\ForumCategories\Tables;

use App\Filament\Support\LocaleTabs;
use App\Models\ForumCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ForumCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->label('Slug'),
                TextColumn::make('title')
                    ->label('Название'),
                TextColumn::make('topics')
                    ->label('Тем')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('posts')
                    ->label('Сообщений')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('icon')
                    ->label('Иконка'),
                TextColumn::make('translations')
                    ->label('Переводы')
                    ->badge()
                    ->state(fn (ForumCategory $record): string => collect(array_keys(LocaleTabs::LOCALES))
                        ->map(fn (string $locale): string => filled($record->getTranslationWithoutFallback('title', $locale))
                            ? mb_strtoupper($locale)
                            : '—')
                        ->implode(' · ')),
                TextColumn::make('sort')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Активно')
                    ->boolean(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->filters([
                TernaryFilter::make('active')
                    ->label('Активно'),
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
