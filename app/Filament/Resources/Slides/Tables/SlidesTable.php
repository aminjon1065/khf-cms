<?php

namespace App\Filament\Resources\Slides\Tables;

use App\Filament\Support\LocaleTabs;
use App\Models\Slide;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['imageAsset.media', 'media']))
            ->columns([
                ImageColumn::make('image')
                    ->label('Изображение')
                    ->state(fn (Slide $record): ?string => $record->imageSet()['thumb'] ?? null)
                    ->height(48),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->limit(40),
                TextColumn::make('category')
                    ->label('Категория')
                    ->badge(),
                TextColumn::make('translations')
                    ->label('Переводы')
                    ->badge()
                    ->state(fn (Slide $record): string => collect(array_keys(LocaleTabs::LOCALES))
                        ->map(fn (string $locale): string => filled($record->getTranslationWithoutFallback('title', $locale))
                            ? mb_strtoupper($locale)
                            : '—')
                        ->implode(' · ')),
                TextColumn::make('news.slug')
                    ->label('Новость')
                    ->placeholder('—'),
                ToggleColumn::make('active')
                    ->label('Активен'),
                TextColumn::make('sort')
                    ->label('Порядок')
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->filters([
                TernaryFilter::make('active')
                    ->label('Активен'),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->beforeReplicaSaved(function (Slide $replica): void {
                        $replica->active = false;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
