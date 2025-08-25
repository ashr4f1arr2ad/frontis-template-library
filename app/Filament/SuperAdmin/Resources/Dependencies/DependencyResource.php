<?php

namespace App\Filament\SuperAdmin\Resources\Dependencies;

use App\Filament\SuperAdmin\Resources\Dependencies\Pages\CreateDependency;
use App\Filament\SuperAdmin\Resources\Dependencies\Pages\EditDependency;
use App\Filament\SuperAdmin\Resources\Dependencies\Pages\ListDependencies;
use App\Filament\SuperAdmin\Resources\Dependencies\Schemas\DependencyForm;
use App\Filament\SuperAdmin\Resources\Dependencies\Tables\DependenciesTable;
use App\Models\Dependency;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DependencyResource extends Resource
{
    protected static ?string $model = Dependency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DependencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DependenciesTable::configure($table);
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
            'index' => ListDependencies::route('/'),
            'create' => CreateDependency::route('/create'),
            'edit' => EditDependency::route('/{record}/edit'),
        ];
    }
}
