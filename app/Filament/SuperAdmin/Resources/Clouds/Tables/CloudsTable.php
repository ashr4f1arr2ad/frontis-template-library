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
            ])
            ->filters([
                // Add filters if needed, e.g., filter by item_type
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->modal() // Enable modal for the view action
                    ->modalContent(fn ($record) => view('filament.superAdmin.resources.clouds.pages.view-cloud', [
                        'record' => $record,
                    ]))
                    ->modalHeading(fn ($record) => "View Cloud: {$record->id}"), // Customize modal heading
                DeleteAction::make()->label('Remove'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            // Optional: Make table rows clickable to open the view modal
            ->recordUrl(null); // Disable default URL navigation
    }
}
