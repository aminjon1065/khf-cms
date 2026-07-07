<?php

namespace App\Filament\Resources\MapRegions\Pages;

use App\Filament\Resources\MapRegions\MapRegionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMapRegions extends ListRecords
{
    protected static string $resource = MapRegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
