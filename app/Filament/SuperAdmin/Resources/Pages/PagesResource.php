<?php

namespace App\Filament\SuperAdmin\Resources\Pages;

use App\Filament\SuperAdmin\Resources\Pages\Pages\CreatePages;
use App\Filament\SuperAdmin\Resources\Pages\Pages\EditPages;
use App\Filament\SuperAdmin\Resources\Pages\Pages\ListPages;
use App\Filament\SuperAdmin\Resources\Pages\Schemas\PagesForm;
use App\Filament\SuperAdmin\Resources\Pages\Tables\PagesTable;
use App\Models\Page;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PagesResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Page';

    public static function form(Schema $schema): Schema
    {
        return PagesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagesTable::configure($table);
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
            'index' => ListPages::route('/'),
            // 'create' => CreatePages::route('/create'),
            // 'edit' => EditPages::route('/{record}/edit'),
        ];
    }
}
