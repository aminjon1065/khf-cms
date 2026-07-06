<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('Пользователь')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('event')
                    ->label('Событие')
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Описание')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('subject_type')
                    ->label('Объект')
                    ->formatStateUsing(fn (?string $state): string => $state ? Str::afterLast($state, '\\') : '—'),
                TextColumn::make('attribute_changes')
                    ->label('Изменения')
                    ->formatStateUsing(function (?object $state): string {
                        if ($state === null || $state->isEmpty()) {
                            return '—';
                        }

                        return collect($state->toArray())
                            ->map(fn (mixed $value, string $key): string => "{$key}: ".json_encode($value, JSON_UNESCAPED_UNICODE))
                            ->implode('; ');
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Событие')
                    ->options([
                        'created' => 'created',
                        'updated' => 'updated',
                        'deleted' => 'deleted',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
