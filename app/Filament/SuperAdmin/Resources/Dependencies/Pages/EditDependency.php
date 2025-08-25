<?php

namespace App\Filament\SuperAdmin\Resources\Dependencies\Pages;

use App\Filament\SuperAdmin\Resources\Dependencies\DependencyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDependency extends EditRecord
{
    protected static string $resource = DependencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
