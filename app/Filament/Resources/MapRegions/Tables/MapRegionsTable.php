<?php

namespace App\Filament\Resources\MapRegions\Tables;

use App\Enums\RiskLevel;
use App\Filament\Support\LocaleTabs;
use App\Models\MapRegion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MapRegionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название'),
                TextColumn::make('risk')
                    ->label('Риск')
                    ->badge(),
                TextInputColumn::make('active_incidents')
                    ->label('Инциденты')
                    ->type('number')
                    // `required` is essential: without it a cleared inline value
                    // passes validation and saves NULL into a NOT NULL column.
                    ->rules(['required', 'integer', 'min:0']),
                TextColumn::make('stations')
                    ->label('Станции')
                    ->sortable(),
                TextColumn::make('translations')
                    ->label('Переводы')
                    ->badge()
                    ->state(fn (MapRegion $record): string => collect(array_keys(LocaleTabs::LOCALES))
                        ->map(fn (string $locale): string => filled($record->getTranslationWithoutFallback('name', $locale))
                            ? mb_strtoupper($locale)
                            : '—')
                        ->implode(' · ')),
                TextColumn::make('sort')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->filters([
                SelectFilter::make('risk')
                    ->label('Риск')
                    ->options(RiskLevel::class),
                TernaryFilter::make('active')
                    ->label('Активен'),
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
