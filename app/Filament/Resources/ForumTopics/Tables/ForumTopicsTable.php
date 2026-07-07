<?php

namespace App\Filament\Resources\ForumTopics\Tables;

use App\Filament\Support\LocaleTabs;
use App\Models\ForumTopic;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ForumTopicsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->limit(50),
                TextColumn::make('category')
                    ->label('Раздел'),
                TextColumn::make('author')
                    ->label('Автор'),
                IconColumn::make('pinned')
                    ->label('Закреплено')
                    ->boolean(),
                TextColumn::make('replies')
                    ->label('Ответов')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('views')
                    ->label('Просмотров')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('translations')
                    ->label('Переводы')
                    ->badge()
                    ->state(fn (ForumTopic $record): string => collect(array_keys(LocaleTabs::LOCALES))
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
                TernaryFilter::make('pinned')
                    ->label('Закреплено'),
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
