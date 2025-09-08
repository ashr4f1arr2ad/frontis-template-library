<?php

namespace App\Filament\SuperAdmin\Resources\Clouds\Pages;

use App\Filament\SuperAdmin\Resources\Clouds\CloudResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCloud extends EditRecord
{
    protected static string $resource = CloudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
