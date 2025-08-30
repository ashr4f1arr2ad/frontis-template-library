<?php

namespace App\Filament\SuperAdmin\Resources\Clouds\Tables;

use App\Models\Page;
use App\Models\Pattern;
use App\Models\Site;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CloudsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => route('filament.superAdmin.resources.users.view', $record->user_id))
            ])
            ->filters([
                // Add filters if needed, e.g., filter by item_type
            ])
            ->recordActions([
                ViewAction::make(),
//                EditAction::make(),
                DeleteAction::make()->label('Remove'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
//                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
