<?php

namespace App\Filament\SuperAdmin\Resources\Clouds\Pages;

use App\Filament\SuperAdmin\Resources\Clouds\CloudResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCloud extends ViewRecord
{
    protected static string $resource = CloudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
