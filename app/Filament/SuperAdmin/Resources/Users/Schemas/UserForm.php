<?php

namespace App\Filament\SuperAdmin\Resources\Users\Schemas;

use App\Models\Tag;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class UserForm
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
            ]);
    }
}
