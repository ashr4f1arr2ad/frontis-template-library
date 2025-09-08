<?php

namespace App\Filament\SuperAdmin\Resources\Patterns\Schemas;

use App\Models\Category;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;
use App\Models\Tag;

class PatternForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(1) // Single column layout
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')
                        ->required()
                        ->unique()
                        ->label('Slug')
                        ->readOnly(),
                    TagsInput::make('tags')
                        ->suggestions([
                            'tailwindcss',
                            'alpinejs',
                            'laravel',
                            'livewire',
                        ]),
                    FileUpload::make('image')
                        ->disk('public')
                        ->directory('patterns')
                        ->required()
                        ->label('Pattern Image')
                        ->uploadingMessage('Uploading image...'),
                    Toggle::make('is_premium')
                        ->label('Premium')
                        ->required(),
                    MarkdownEditor::make('description'),
                    Textarea::make('pattern_json')
                        ->required()
                        ->label('Patterns JSON'),
                    Select::make('categories')
                        ->multiple()
                        ->relationship('categories', 'name')
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->unique(table: Category::class),
                        ]),
                ]),
            ]);
    }
}
