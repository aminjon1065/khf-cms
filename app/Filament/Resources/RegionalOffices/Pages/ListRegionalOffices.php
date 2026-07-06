<?php

namespace App\Filament\Resources\RegionalOffices\Pages;

use App\Filament\Resources\RegionalOffices\RegionalOfficeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegionalOffices extends ListRecords
{
    protected static string $resource = RegionalOfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
