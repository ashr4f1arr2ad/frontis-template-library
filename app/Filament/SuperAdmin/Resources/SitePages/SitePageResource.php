<?php

namespace App\Filament\SuperAdmin\Resources\SitePages;

use App\Filament\SuperAdmin\Resources\SitePages\Pages\CreateSitePage;
use App\Filament\SuperAdmin\Resources\SitePages\Pages\EditSitePage;
use App\Filament\SuperAdmin\Resources\SitePages\Pages\ListSitePages;
use App\Filament\SuperAdmin\Resources\SitePages\Schemas\SitePageForm;
use App\Filament\SuperAdmin\Resources\SitePages\Tables\SitePagesTable;
use App\Models\SitePage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SitePageResource extends Resource
{
    protected static ?string $model = SitePage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'SitePage';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SitePageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SitePagesTable::configure($table);
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
            'index' => ListSitePages::route('/'),
            // 'create' => CreateSitePage::route('/create'),
            // 'edit' => EditSitePage::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'Sites Management';
    }
}
