<?php

namespace App\Filament\SuperAdmin\Resources\Patterns\Schemas;

use Filament\Forms\Components\Textarea;
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
                MarkdownEditor::make('description'),
                Toggle::make('is_premium')
                    ->label('Premium')
                    ->required(),
                FileUpload::make('image')
                    ->required()
                    ->label('Pattern Image')
                    ->uploadingMessage('Uploading image...'),
                Textarea::make('patterns')
                    ->required()
                    ->label('Patterns JSON'),
                Select::make('tags')
                    ->multiple()
                    ->relationship('tags', 'name')
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->unique(table: Tag::class),
                    ]),
            ]);
    }
}
