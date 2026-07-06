<?php

namespace App\Filament\Resources\Leaders\Pages;

use App\Filament\Concerns\HandlesTranslatableForm;
use App\Filament\Resources\Leaders\LeaderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeader extends EditRecord
{
    use HandlesTranslatableForm;

    protected static string $resource = LeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->translatableAttributes() as $attribute) {
            $data[$attribute] = $this->getRecord()->getTranslations($attribute);
        }

        return $data;
    }
}
