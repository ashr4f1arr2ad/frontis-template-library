<?php

namespace App\Filament\SuperAdmin\Resources\Patterns\Pages;

use App\Filament\SuperAdmin\Resources\Patterns\PatternResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPatterns extends ListRecords
{
    protected static string $resource = PatternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
