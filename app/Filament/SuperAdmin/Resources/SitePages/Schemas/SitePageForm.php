<?php

namespace App\Filament\SuperAdmin\Resources\SitePages\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use App\Models\Site;

class SitePageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')
                ->label('Select Site')
                ->options(Site::query()->orderBy('title')->pluck('title', 'id'))
                ->searchable()
                ->required()->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    $site = Site::find($state);
                    $set('site_slug', $site?->slug);
                }),
                TextInput::make('site_slug')
                ->unique()
                ->label('Site Slug')
                ->readOnly(),
                Repeater::make('pages')
                        ->schema([
                            TextInput::make('name')
                            ->required(),
                            TextInput::make('template'),
                            TextInput::make('badge'),
                            Textarea::make('page')
                            ->required()
                            ->rows(8)
                            ->label('Page JSON')->columnSpanFull(),
                            TextInput::make('featured_image')->url()
                    ])->columns(1)->columnSpanFull()->collapsed()->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
            ]);
    }
}
