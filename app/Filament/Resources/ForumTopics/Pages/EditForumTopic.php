<?php

namespace App\Filament\Resources\ForumTopics\Pages;

use App\Filament\Concerns\HandlesTranslatableForm;
use App\Filament\Resources\ForumTopics\ForumTopicResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditForumTopic extends EditRecord
{
    use HandlesTranslatableForm;

    protected static string $resource = ForumTopicResource::class;

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
