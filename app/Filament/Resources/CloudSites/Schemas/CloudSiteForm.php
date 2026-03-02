<?php

namespace App\Filament\Resources\CloudSites\Schemas;

use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\FeatureFlag;

class CloudSiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Action::make('github_connect')
                ->size(Size::Large)
    ->button()
            ]);
    }
}
