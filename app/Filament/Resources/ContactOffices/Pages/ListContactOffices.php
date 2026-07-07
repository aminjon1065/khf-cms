<?php

namespace App\Filament\Resources\ContactOffices\Pages;

use App\Filament\Resources\ContactOffices\ContactOfficeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactOffices extends ListRecords
{
    protected static string $resource = ContactOfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
