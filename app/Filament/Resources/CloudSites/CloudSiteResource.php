<?php

namespace App\Filament\Resources\CloudSites;

use App\Filament\Resources\CloudSites\Pages\CreateCloudSite;
use App\Filament\Resources\CloudSites\Pages\EditCloudSite;
use App\Filament\Resources\CloudSites\Pages\ListCloudSites;
use App\Filament\Resources\CloudSites\Schemas\CloudSiteForm;
use App\Filament\Resources\CloudSites\Tables\CloudSitesTable;
use App\Models\CloudSite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CloudSiteResource extends Resource
{
    protected static ?string $model = CloudSite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // protected static ?string $recordTitleAttribute = 'CloudSite';

    public static function form(Schema $schema): Schema
    {
        return CloudSiteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CloudSitesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCloudSites::route('/'),
            // 'create' => CreateCloudSite::route('/create'),
            // 'edit' => EditCloudSite::route('/{record}/edit'),
        ];
    }
}
