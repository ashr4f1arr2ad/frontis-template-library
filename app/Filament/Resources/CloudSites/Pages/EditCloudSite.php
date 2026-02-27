<?php

namespace App\Filament\Resources\CloudSites\Pages;

use App\Filament\Resources\CloudSites\CloudSiteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCloudSite extends EditRecord
{
    protected static string $resource = CloudSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
