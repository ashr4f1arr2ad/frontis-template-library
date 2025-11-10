<?php

namespace App\Filament\SuperAdmin\Resources\Sites\Schemas;

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
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;

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
                    ->label('Content')
                    ->rows(8)->columnSpanFull(),
                TagsInput::make('tags')
                    ->suggestions([
                        'tailwindcss',
                        'alpinejs',
                        'laravel',
                        'livewire',
                    ])->required()->columnSpanFull(),
                Select::make('categories')
                    ->multiple()
                    ->relationship('categories', 'name')
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->unique(table: Category::class),
                    ])->required()->columns(3)->columnSpanFull(),
                MarkdownEditor::make('description')->required()->columnSpanFull(),
                FileUpload::make('image')
                    ->required()
                    ->disk('public')
                    ->directory('sites')
                    ->label('Site Image')
                    ->uploadingMessage('Uploading image...')->columnSpanFull(),
                TextInput::make('preview_url')
                    ->url()
                    ->required()
                    ->label('Preview Url')->columnSpanFull(),
                TextInput::make('read_more_url')
                    ->url()
                    ->label('Read More Url')->columnSpanFull(),
                TextInput::make('uploads_url')
                    ->url()
                    ->label('Uploads Url')->columnSpanFull(),
                Repeater::make('dependencies')
                        ->schema([
                            TextInput::make('name')
                            ->required(),
                            TextInput::make('slug')
                            ->required(),
                            TextInput::make('version')->required()
                        ])->columns(3)->columnSpanFull(),
                Section::make()
                        ->schema([
                            Textarea::make('colors')
                            ->required()
                            ->rows(8)
                            ->label('Color JSON')->columnSpanFull(),
                    ])->columns(1)->columnSpanFull(),
                Section::make()
                    ->schema([
                        Textarea::make('color_gradients')
                        ->rows(8)
                        ->label('Color Gradient JSON')->columnSpanFull(),
                    ])->columns(1)->columnSpanFull(),
                Section::make()
                    ->schema([
                        Textarea::make('typographies')
                        ->required()
                        ->rows(8)
                        ->label('Typographies JSON')->columnSpanFull(),
                    ])->columns(1)->columnSpanFull(),
                Section::make()
                    ->schema([
                        Textarea::make('custom_typographies')
                        ->rows(8)
                        ->label('Custom Typographies JSON')->columnSpanFull(),
                    ])->columns(1)->columnSpanFull(),
                Repeater::make('pages')
                        ->schema([
                            TextInput::make('name')
                            ->required(),
                            Textarea::make('page')
                            ->required()
                            ->rows(8)
                            ->label('Page JSON')->columnSpanFull(),
                    ])->columns(1)->columnSpanFull(),
                Toggle::make('is_premium')
                    ->label('Premium'),
            ]);
    }
}
