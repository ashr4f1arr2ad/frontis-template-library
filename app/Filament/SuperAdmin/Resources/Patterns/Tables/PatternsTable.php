<?php

namespace App\Filament\SuperAdmin\Resources\Patterns\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Tag;

class PatternsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->html()
                    ->sortable(),
                BooleanColumn::make('is_premium')
                    ->label('Premium')
                    ->sortable(),
                TagsColumn::make('tags')
                    ->label('Tags')
                    ->separator(',')
            ])
            ->filters([
        SelectFilter::make('is_premium')
            ->label('Premium Status')
            ->options([
                '1' => 'Premium',
                '0' => 'Non-Premium',
            ])
    ])
        ->recordActions([
            EditAction::make()->closeModalByClickingAway(false),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
    }
}
