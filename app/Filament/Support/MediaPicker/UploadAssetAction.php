<?php

namespace App\Filament\Support\MediaPicker;

use App\Models\MediaAsset;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * A hint action attached to a media ModalTableSelect that uploads new file(s)
 * straight into the library and selects them — WordPress-style "upload or pick
 * from library". Restricted to images or documents to match the picker.
 */
class UploadAssetAction
{
    public static function make(bool $image): Action
    {
        $mimes = $image ? MediaAsset::IMAGE_MIMES : MediaAsset::DOCUMENT_MIMES;

        return Action::make('uploadNew')
            ->label('Загрузить новый')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->modalHeading('Загрузка в медиатеку')
            ->modalSubmitActionLabel('Загрузить и выбрать')
            ->schema([
                FileUpload::make('files')
                    ->label($image ? 'Изображения' : 'Документы')
                    ->multiple()
                    ->storeFiles(false)
                    ->required()
                    ->maxSize(51200) // 50 МБ (ToR §5.4)
                    ->acceptedFileTypes($mimes)
                    ->helperText('Файлы попадут в медиатеку и будут доступны для повторного использования.'),
            ])
            ->action(function (array $data, ModalTableSelect $component): void {
                $ids = MediaAsset::createFromUploads($data['files'] ?? [])
                    ->pluck('id')
                    ->map(fn (int $id): string => (string) $id)
                    ->all();

                if ($ids === []) {
                    return;
                }

                if ($component->isMultiple()) {
                    $current = array_values(array_filter((array) $component->getState()));
                    $component->state([...$current, ...$ids]);
                } else {
                    $component->state($ids[0]);
                }

                Notification::make()
                    ->success()
                    ->title('Загружено и выбрано: '.count($ids))
                    ->send();
            });
    }
}
