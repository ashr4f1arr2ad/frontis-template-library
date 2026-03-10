<?php

namespace App\Filament\SuperAdmin\Resources\SitePages\Pages;

use App\Filament\SuperAdmin\Resources\SitePages\SitePageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSitePage extends EditRecord
{
    protected static string $resource = SitePageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
