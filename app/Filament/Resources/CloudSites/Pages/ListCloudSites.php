<?php

namespace App\Filament\Resources\CloudSites\Pages;

use App\Filament\Resources\CloudSites\CloudSiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCloudSites extends ListRecords
{
    protected static string $resource = CloudSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
