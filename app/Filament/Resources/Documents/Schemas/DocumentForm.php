<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\DocType;
use App\Enums\DocumentCategory;
use App\Filament\Support\LocaleTabs;
use App\Filament\Support\MediaPicker\DocumentAssetPickerTable;
use App\Filament\Support\MediaPicker\UploadAssetAction;
use App\Models\Document;
use App\Rules\MediaAssetKind;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                LocaleTabs::text('title', 'Заголовок'),
                Select::make('category')
                    ->label('Категория')
                    ->options(DocumentCategory::class)
                    ->native(false)
                    ->required(),
                TextInput::make('number')
                    ->label('Номер')
                    ->maxLength(255),
                DatePicker::make('document_date')
                    ->label('Дата документа')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->required(),
                ModalTableSelect::make('media_asset_id')
                    ->label('Файл из медиатеки')
                    ->relationship('mediaAsset', 'name')
                    ->tableConfiguration(DocumentAssetPickerTable::class)
                    ->rule(MediaAssetKind::document())
                    ->hintAction(UploadAssetAction::make(image: false))
                    ->helperText('Выберите документ из медиатеки или загрузите новый (можно переиспользовать).'),
                FileUpload::make('file_path')
                    ->label('Или загрузить файл')
                    ->disk(Document::DISK)
                    ->directory('documents')
                    ->downloadable()
                    ->openable()
                    ->maxSize(51200) // 50 МБ (ToR §5.4)
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->helperText('PDF, DOCX или XLSX (до 50 МБ). Тип и размер определяются автоматически.'),
                Select::make('type')
                    ->label('Тип файла')
                    ->options(DocType::class)
                    ->native(false)
                    // Auto-derived from an uploaded file; required only for a link/placeholder
                    // document without a file, so the API never serves a null type.
                    ->required(fn (Get $get): bool => blank($get('file_path')) && blank($get('media_asset_id')))
                    ->helperText('Определяется автоматически при загрузке файла; укажите вручную, если файла нет.'),
                TextInput::make('size')
                    ->label('Размер')
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => blank($get('file_path')) && blank($get('media_asset_id')))
                    ->helperText('Определяется автоматически при загрузке файла; укажите вручную, если файла нет.'),
                TextInput::make('sort')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
