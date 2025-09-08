<?php

namespace App\Filament\SuperAdmin\Resources\Patterns;

use App\Filament\SuperAdmin\Resources\Patterns\Pages\CreatePattern;
use App\Filament\SuperAdmin\Resources\Patterns\Pages\EditPattern;
use App\Filament\SuperAdmin\Resources\Patterns\Pages\ListPatterns;
use App\Filament\SuperAdmin\Resources\Patterns\Schemas\PatternForm;
use App\Filament\SuperAdmin\Resources\Patterns\Tables\PatternsTable;
use App\Models\Pattern;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PatternResource extends Resource
{
    protected static ?string $model = Pattern::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PatternForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatternsTable::configure($table);
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
            'index' => ListPatterns::route('/'),
//            'create' => CreatePattern::route('/create'),
//            'edit' => EditPattern::route('/{record}/edit'),
        ];
    }
}
