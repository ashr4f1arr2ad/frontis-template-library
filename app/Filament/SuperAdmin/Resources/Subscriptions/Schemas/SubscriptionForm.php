<?php

namespace App\Filament\SuperAdmin\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
//        total_sites
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('type')->label('Subscription type')->required(),
                TextInput::make('total_sites')
                    ->label('Total sites')
                    ->required()
                    ->numeric(),
            ]);
    }
}
