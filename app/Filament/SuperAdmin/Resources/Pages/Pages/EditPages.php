<?php

namespace App\Filament\SuperAdmin\Resources\Pages\Pages;

use App\Filament\SuperAdmin\Resources\Pages\PagesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPages extends EditRecord
{
    protected static string $resource = PagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
