<?php

namespace App\Filament\SuperAdmin\Resources\Sites\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required(),
                TextInput::make('slug')->required(),
                Textarea::make('description')->required(),
                TextInput::make('content')->required(),
                FileUpload::make('image')->required(),
                Toggle::make('isPro')->inline(false)
            ]);
    }
}
