<?php

namespace App\Filament\SuperAdmin\Resources\Tags\Pages;

use App\Filament\SuperAdmin\Resources\Tags\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;
}
