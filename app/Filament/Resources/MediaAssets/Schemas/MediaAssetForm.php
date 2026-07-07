<?php

namespace App\Filament\Resources\MediaAssets\Schemas;

use App\Models\MediaAsset;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('file')
                    ->label('Файл')
                    ->collection(MediaAsset::COLLECTION)
                    ->downloadable()
                    ->openable()
                    ->maxSize(51200) // 50 МБ (ToR §5.4)
                    ->acceptedFileTypes(MediaAsset::ACCEPTED_MIMES)
                    ->required()
                    ->helperText('Изображение (JPEG/PNG/WebP) или документ (PDF/DOCX/XLSX), до 50 МБ.'),
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Как файл будет называться в медиатеке при выборе.'),
                TextInput::make('alt')
                    ->label('Alt-текст (для изображений)')
                    ->maxLength(255)
                    ->helperText('Описание изображения для доступности и SEO.'),
            ]);
    }
}
