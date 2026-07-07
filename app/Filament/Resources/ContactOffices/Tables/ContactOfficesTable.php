<?php

namespace App\Filament\Resources\ContactOffices\Tables;

use App\Filament\Support\LocaleTabs;
use App\Models\ContactOffice;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactOfficesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('region')
                    ->label('Регион / офис'),
                TextColumn::make('phone')
                    ->label('Телефон'),
                TextColumn::make('email')
                    ->label('Эл. почта'),
                IconColumn::make('is_head')
                    ->label('Головной')
                    ->boolean(),
                TextColumn::make('translations')
                    ->label('Переводы')
                    ->badge()
                    ->state(fn (ContactOffice $record): string => collect(array_keys(LocaleTabs::LOCALES))
                        ->map(fn (string $locale): string => filled($record->getTranslationWithoutFallback('region', $locale))
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
                TernaryFilter::make('is_head')
                    ->label('Головной офис'),
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
