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
                    ->label('Content')
                    ->rows(8)->columnSpanFull(),
                    TagsInput::make('tags')
                    ->suggestions([
                        'tailwindcss',
                        'alpinejs',
                        'laravel',
                        'livewire',
                    ])->columnSpanFull(),
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
                            ])->columns(1)->columnSpanFull(),
                    Repeater::make('colors')
                            ->schema([
                                TextInput::make('name')
                                ->required(),
                                TextInput::make('variable')
                                ->required(),
                                TextInput::make('value')->required()
                            ])->columns(1)->columnSpanFull(),
                    Repeater::make('typographies')
                            ->schema([
                                TextInput::make('name')
                                ->required(),
                                TextInput::make('slug')
                                ->required(),
                                TextInput::make('value')->required()
                            ])->columns(1)->columnSpanFull(),
                    Repeater::make('pages')
                            ->schema([
                                TextInput::make('name')
                                ->required(),
                                // TextInput::make('slug')
                                // ->required(),
                                Textarea::make('sites')
                                ->required()
                                ->rows(8)
                                ->label('Sites JSON')->columnSpanFull(),
                            ])->columns(1)->columnSpanFull(),
                    Toggle::make('is_premium')
                        ->label('Premium')
                        ->required(),
            ]);
    }
}
