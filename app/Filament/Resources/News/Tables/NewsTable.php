<?php

namespace App\Filament\Resources\News\Tables;

use App\Enums\NewsStatus;
use App\Filament\Support\LocaleTabs;
use App\Models\News;
use App\Models\NewsCategory;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->limit(40)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where('title->tj', 'like', "%{$search}%")
                        ->orWhere('title->ru', 'like', "%{$search}%")
                        ->orWhere('title->en', 'like', "%{$search}%")),
                TextColumn::make('category.label')
                    ->label('Категория')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('translations')
                    ->label('Переводы')
                    ->badge()
                    ->state(fn (News $record): string => collect(array_keys(LocaleTabs::LOCALES))
                        ->map(fn (string $locale): string => filled($record->getTranslationWithoutFallback('title', $locale))
                            ? mb_strtoupper($locale)
                            : '—')
                        ->implode(' · ')),
                TextColumn::make('published_at')
                    ->label('Опубликовано')
                    ->dateTime('d.m.Y')
                    ->sortable(),
                TextColumn::make('views')
                    ->label('Просмотры')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(NewsStatus::class),
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->options(fn (): array => NewsCategory::query()->orderBy('sort')->get()->pluck('label', 'id')->all()),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->beforeReplicaSaved(function (News $replica): void {
                        $replica->slug = null; // regenerate a unique slug from the title
                        $replica->status = NewsStatus::Draft;
                        $replica->published_at = null;
                        $replica->views = 0;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Опубликовать')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (News $record) => $record->update([
                            'status' => NewsStatus::Published,
                            'published_at' => $record->published_at ?? now(),
                        ])))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('archive')
                        ->label('В архив')
                        ->icon('heroicon-o-archive-box')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (News $record) => $record->update([
                            'status' => NewsStatus::Archived,
                        ])))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
