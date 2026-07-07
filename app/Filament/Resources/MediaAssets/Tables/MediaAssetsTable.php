<?php

namespace App\Filament\Resources\MediaAssets\Tables;

use App\Enums\DocType;
use App\Models\MediaAsset;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('preview')
                    ->label('Превью')
                    ->collection(MediaAsset::COLLECTION)
                    ->conversion('thumb')
                    ->height(48),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kind')
                    ->label('Тип')
                    ->badge()
                    ->state(fn (MediaAsset $record): string => match ($record->kind()) {
                        'image' => 'Изображение',
                        'document' => $record->docType()?->apiValue() ?? 'Документ',
                        default => '—',
                    })
                    ->color(fn (MediaAsset $record): string => match ($record->docType()) {
                        DocType::Pdf => 'danger',
                        DocType::Docx => 'info',
                        DocType::Xlsx => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('size')
                    ->label('Размер')
                    ->state(fn (MediaAsset $record): string => $record->humanSize()),
                TextColumn::make('created_at')
                    ->label('Загружен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label('Тип')
                    ->options([
                        'image' => 'Изображения',
                        'document' => 'Документы',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'image' => $query->whereHas('media', fn ($q) => $q->where('mime_type', 'like', 'image/%')),
                            'document' => $query->whereHas('media', fn ($q) => $q->where('mime_type', 'not like', 'image/%')),
                            default => $query,
                        };
                    }),
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
