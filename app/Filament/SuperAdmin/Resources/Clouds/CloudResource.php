<?php

namespace App\Filament\SuperAdmin\Resources\Clouds;

use App\Filament\SuperAdmin\Resources\Clouds\Pages\CreateCloud;
use App\Filament\SuperAdmin\Resources\Clouds\Pages\EditCloud;
use App\Filament\SuperAdmin\Resources\Clouds\Pages\ListClouds;
use App\Filament\SuperAdmin\Resources\Clouds\Schemas\CloudForm;
use App\Filament\SuperAdmin\Resources\Clouds\Tables\CloudsTable;
use App\Models\Cloud;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CloudResource extends Resource
{
    protected static ?string $model = Cloud::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CloudForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CloudsTable::configure($table);
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
            'index' => ListClouds::route('/'),
//            'create' => CreateCloud::route('/create'),
//            'edit' => EditCloud::route('/{record}/edit'),
        ];
    }
}
