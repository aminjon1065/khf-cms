<?php

namespace App\Filament\Resources\Hotlines\Pages;

use App\Filament\Resources\Hotlines\HotlineResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHotlines extends ListRecords
{
    protected static string $resource = HotlineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
