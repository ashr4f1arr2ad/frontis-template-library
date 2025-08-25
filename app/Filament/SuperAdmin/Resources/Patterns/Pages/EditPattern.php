<?php

namespace App\Filament\SuperAdmin\Resources\Patterns\Pages;

use App\Filament\SuperAdmin\Resources\Patterns\PatternResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPattern extends EditRecord
{
    protected static string $resource = PatternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
