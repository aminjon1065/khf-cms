<?php

namespace App\Filament\Resources\RegionalOffices\Pages;

use App\Filament\Concerns\HandlesTranslatableForm;
use App\Filament\Resources\RegionalOffices\RegionalOfficeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegionalOffice extends EditRecord
{
    use HandlesTranslatableForm;

    protected static string $resource = RegionalOfficeResource::class;

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
