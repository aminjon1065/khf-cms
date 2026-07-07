<?php

namespace App\Filament\Resources\Directions\Pages;

use App\Filament\Concerns\HandlesTranslatableForm;
use App\Filament\Resources\Directions\DirectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDirection extends EditRecord
{
    use HandlesTranslatableForm;

    protected static string $resource = DirectionResource::class;

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
