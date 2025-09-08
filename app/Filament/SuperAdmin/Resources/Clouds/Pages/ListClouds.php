<?php

namespace App\Filament\SuperAdmin\Resources\Clouds\Pages;

use App\Filament\SuperAdmin\Resources\Clouds\CloudResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClouds extends ListRecords
{
    protected static string $resource = CloudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
