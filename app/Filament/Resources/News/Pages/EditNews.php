<?php

namespace App\Filament\Resources\News\Pages;

use App\Enums\NewsStatus;
use App\Filament\Concerns\HandlesTranslatableForm;
use App\Filament\Resources\News\NewsResource;
use App\Models\News;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditNews extends EditRecord
{
    use HandlesTranslatableForm;

    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->translatableAttributes() as $attribute) {
            $data[$attribute] = $this->getRecord()->getTranslations($attribute);
        }

        return $data;
    }

    /**
     * Periodic draft autosave (polled every 30s by the form's draft-autosave
     * view). Only drafts are saved silently; an invalid form is skipped and
     * retried on the next tick.
     */
    public function autosaveDraft(): void
    {
        if (! $this->record instanceof News || $this->record->status !== NewsStatus::Draft) {
            return;
        }

        try {
            $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
        } catch (ValidationException) {
            // Keep editing; the next poll will try again once the form is valid.
        }
    }
}
