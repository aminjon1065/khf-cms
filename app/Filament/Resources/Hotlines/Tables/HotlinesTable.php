<?php

namespace App\Filament\Resources\Hotlines\Tables;

use App\Filament\Support\LocaleTabs;
use App\Models\Hotline;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HotlinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Номер'),
                TextColumn::make('label')
                    ->label('Название'),
                TextColumn::make('translations')
                    ->label('Переводы')
                    ->badge()
                    ->state(fn (Hotline $record): string => collect(array_keys(LocaleTabs::LOCALES))
                        ->map(fn (string $locale): string => filled($record->getTranslationWithoutFallback('label', $locale))
                            ? mb_strtoupper($locale)
                            : '—')
                        ->implode(' · ')),
                IconColumn::make('is_primary')
                    ->label('Главная')
                    ->boolean(),
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
