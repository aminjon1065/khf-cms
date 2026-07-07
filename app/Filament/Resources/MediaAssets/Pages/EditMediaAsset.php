<?php

namespace App\Filament\Resources\MediaAssets\Pages;

use App\Filament\Resources\MediaAssets\MediaAssetResource;
use App\Models\MediaAsset;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMediaAsset extends EditRecord
{
    protected static string $resource = MediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (MediaAsset $record, Action $action): void {
                    if ($record->isInUse()) {
                        Notification::make()
                            ->danger()
                            ->title('Файл используется')
                            ->body('Этот файл прикреплён к новости или документу. Открепите его перед удалением.')
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
