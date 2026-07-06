<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var User $user */
        $user = $this->record;
        $data['role'] = $user->roles->first()?->name ?? 'editor';

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['password_confirmation']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var User $user */
        $user = $this->record;
        $role = $this->form->getState()['role'] ?? null;

        if ($role !== null) {
            $user->syncRoles([$role]);

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties(['role' => $role])
                ->event('updated')
                ->log('Роль пользователя обновлена');
        }
    }
}
