<?php

namespace App\Filament\SuperAdmin\Resources\Pages\Schemas;

use App\Models\Category;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;

class PagesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
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
                    ->label('Content'),
                TextInput::make('preview_url')
                    ->url()
                    ->label('Preview Url')->columnSpanFull(),
                TextInput::make('read_more_url')
                    ->url()
                    ->label('Read More Url')->columnSpanFull(),
                Repeater::make('dependencies')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('slug')
                            ->required(),
                        TextInput::make('version')->required()
                    ])->columns(3)->columnSpanFull(),
                TagsInput::make('tags')
                    ->suggestions([
                        'tailwindcss',
                        'alpinejs',
                        'laravel',
                        'livewire',
                    ]),
                Select::make('categories')
                    ->multiple()
                    ->relationship('categories', 'name')
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->unique(table: Category::class),
                    ]),
                Toggle::make('is_premium')
                    ->label('Premium')->columns(3)->columnSpanFull(),
                MarkdownEditor::make('description')->columnSpanFull(),
                FileUpload::make('image')
                    ->required()
                    ->disk('public')
                    ->directory('pages')
                    ->visibility('public')
                    ->label('Image')
                    ->uploadingMessage('Uploading image...')->columnSpanFull(),
                Textarea::make('page_json')
                    ->required()
                    ->label('Page JSON'),
            ]);
    }
}
