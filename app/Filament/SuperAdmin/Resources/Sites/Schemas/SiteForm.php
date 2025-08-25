<?php

namespace App\Filament\SuperAdmin\Resources\Sites\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use App\Models\Tag;
use Illuminate\Support\Str;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')
                    ->required()
                    ->unique()
                    ->label('Slug')
                    ->readOnly(),
                    Textarea::make('content')
                    ->required()
                    ->label('Content'),
                    TagsInput::make('tags')
                    ->suggestions([
                        'tailwindcss',
                        'alpinejs',
                        'laravel',
                        'livewire',
                    ]),
                    MarkdownEditor::make('description')->columnSpanFull(),
                    FileUpload::make('image')
                    ->required()
                    ->label('Site Image')
                    ->uploadingMessage('Uploading image...')->columnSpanFull(),
                    Repeater::make('dependencies')
                            ->schema([
                                TextInput::make('name')
                                ->required(),
                                TextInput::make('slug')
                                ->required(),
                                TextInput::make('version')->required()
                            ])->columns(2)->columnSpanFull(),
                            Toggle::make('is_premium')
                    ->label('Premium')
                    ->required(),
            ]);
    }
}
