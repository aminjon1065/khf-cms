<?php

namespace App\Filament\Resources\MediaAssets\Pages;

use App\Filament\Resources\MediaAssets\MediaAssetResource;
use App\Models\MediaAsset;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMediaAssets extends ListRecords
{
    protected static string $resource = MediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulkUpload')
                ->label('Загрузить файлы')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->modalHeading('Загрузка файлов в медиатеку')
                ->modalSubmitActionLabel('Загрузить')
                ->schema([
                    FileUpload::make('files')
                        ->label('Файлы')
                        ->multiple()
                        ->storeFiles(false)
                        ->required()
                        ->maxSize(51200) // 50 МБ (ToR §5.4)
                        ->acceptedFileTypes(MediaAsset::ACCEPTED_MIMES)
                        ->helperText('Можно выбрать сразу несколько изображений или документов — для каждого создастся отдельный элемент медиатеки.'),
                ])
                ->action(function (array $data): void {
                    $count = MediaAsset::createFromUploads($data['files'] ?? [])->count();

                    Notification::make()
                        ->success()
                        ->title('Загружено файлов: '.$count)
                        ->send();
                }),
            CreateAction::make()->label('Один файл (с названием)'),
        ];
    }
}
