<?php

namespace App\Filament\Support\MediaPicker;

use App\Enums\DocType;
use App\Models\MediaAsset;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Shared column set for the media-library modal pickers (ModalTableSelect).
 * Images show a thumbnail, documents a type badge; searchable by name.
 */
class MediaAssetPickerTable
{
    public static function configure(Table $table): Table
    {
        return self::base($table);
    }

    public static function base(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('preview')
                    ->label('Превью')
                    ->state(fn (MediaAsset $record): ?string => $record->previewUrl())
                    ->height(56)
                    ->extraImgAttributes(['loading' => 'lazy']),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->state(fn (MediaAsset $record): string => $record->isImage()
                        ? 'Изображение'
                        : ($record->docType()?->apiValue() ?? 'Файл'))
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
            ]);
    }
}
