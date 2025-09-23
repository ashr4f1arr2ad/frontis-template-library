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
                    ->required()
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
                Repeater::make('dependencies')
                        ->schema([
                            TextInput::make('name')
                            ->required(),
                            TextInput::make('slug')
                            ->required(),
                            TextInput::make('version')->required()
                        ])->columns(3)->columnSpanFull(),
                Repeater::make('colors')
                        ->schema([
                            TextInput::make('name')
                            ->required(),
                            TextInput::make('slug')
                            ->required(),
                            ColorPicker::make('color')->required()
                        ])->columns(3)->columnSpanFull(),
                Repeater::make('color_gradients')
                        ->schema([
                            TextInput::make('name')
                            ->required(),
                            TextInput::make('slug')
                            ->required(),
                            TextInput::make('gradient')
                            ->required(),
                        ])->columns(3)->columnSpanFull(),
                Repeater::make('typographies')
                        ->schema([
                            TextInput::make('name')
                            ->required(),
                            Grid::make([
                                'default' => 1,
                                'sm' => 2,
                                'md' => 3,
                                'lg' => 4,
                                'xl' => 3,
                            ])
                            ->schema([
                                TextInput::make('fontFamily')
                                        ->label('Font Family'),
                                Select::make('fontWeight')
                                        ->options([
                                            'Default'=>'Default','100'=>'Thin','200'=>'Extra Light','300'=>'Light',
                                            '400'=>'Normal','500'=>'Medium','600'=>'Semi Bold','700'=>'Bold',
                                            '800'=>'Extra Bold','900'=>'Black'
                                        ])
                                        ->default('Default'),
                                Select::make('fontStyle')
                                        ->options(['Default'=>'Default','normal'=>'Normal','italic'=>'Italic','oblique'=>'Oblique'])
                                        ->default('Default'),
                                Select::make('textTransform')
                                        ->options(['Default'=>'Default','none'=>'None','capitalize'=>'Capitalize','uppercase'=>'Uppercase','lowercase'=>'Lowercase'])
                                        ->default('Default'),
                                Select::make('textDecoration')
                                        ->options(['Default'=>'Default','none'=>'None','underline'=>'Underline','overline'=>'Overline','line-through'=>'Line Through'])
                                        ->default('Default'),
                            ]),
                            Fieldset::make('Font Size')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 3,
                                    ])
                                    ->schema([
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('fontSize.Desktop')->label('Desktop')->numeric(),
                                                    Select::make('fontSizeUnit.Desktop')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('fontSize.Tablet')->label('Tablet')->numeric(),
                                                    Select::make('fontSizeUnit.Tablet')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('fontSize.Mobile')->label('Mobile')->numeric(),
                                                    Select::make('fontSizeUnit.Mobile')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                    ]),
                                    Fieldset::make('Line Height')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 3,
                                    ])
                                    ->schema([
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('lineHeight.Desktop')->label('Desktop')->numeric(),
                                                    Select::make('lineHeightUnits.Desktop')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('lineHeight.Tablet')->label('Tablet')->numeric(),
                                                    Select::make('lineHeightUnits.Tablet')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('lineHeight.Mobile')->label('Mobile')->numeric(),
                                                    Select::make('lineHeightUnits.Mobile')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                    ]),
                                    Fieldset::make('Letter Spacing')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 3,
                                    ])
                                    ->schema([
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('letterSpacing.Desktop')->label('Desktop')->numeric(),
                                                    Select::make('letterSpacingUnit.Desktop')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('letterSpacing.Tablet')->label('Tablet')->numeric(),
                                                    Select::make('letterSpacingUnit.Tablet')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('letterSpacing.Mobile')->label('Mobile')->numeric(),
                                                    Select::make('letterSpacingUnit.Mobile')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                    ]),
                        ])->columns(1)->columnSpanFull(),
                Repeater::make('custom_typographies')
                        ->schema([
                            TextInput::make('name')
                            ->required(),
                            Grid::make([
                                'default' => 1,
                                'sm' => 2,
                                'md' => 3,
                                'lg' => 4,
                                'xl' => 3,
                            ])
                            ->schema([
                                TextInput::make('fontFamily')
                                        ->label('Font Family'),
                                Select::make('fontWeight')
                                        ->options([
                                            'Default'=>'Default','100'=>'Thin','200'=>'Extra Light','300'=>'Light',
                                            '400'=>'Normal','500'=>'Medium','600'=>'Semi Bold','700'=>'Bold',
                                            '800'=>'Extra Bold','900'=>'Black'
                                        ])
                                        ->default('Default'),
                                Select::make('fontStyle')
                                        ->options(['Default'=>'Default','normal'=>'Normal','italic'=>'Italic','oblique'=>'Oblique'])
                                        ->default('Default'),
                                Select::make('textTransform')
                                        ->options(['Default'=>'Default','none'=>'None','capitalize'=>'Capitalize','uppercase'=>'Uppercase','lowercase'=>'Lowercase'])
                                        ->default('Default'),
                                Select::make('textDecoration')
                                        ->options(['Default'=>'Default','none'=>'None','underline'=>'Underline','overline'=>'Overline','line-through'=>'Line Through'])
                                        ->default('Default'),
                            ]),
                            Fieldset::make('Font Size')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 3,
                                    ])
                                    ->schema([
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('fontSize.Desktop')->label('Desktop')->numeric(),
                                                    Select::make('fontSizeUnit.Desktop')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('fontSize.Tablet')->label('Tablet')->numeric(),
                                                    Select::make('fontSizeUnit.Tablet')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('fontSize.Mobile')->label('Mobile')->numeric(),
                                                    Select::make('fontSizeUnit.Mobile')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                    ]),
                            Fieldset::make('Line Height')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 3,
                                    ])
                                    ->schema([
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('lineHeight.Desktop')->label('Desktop')->numeric(),
                                                    Select::make('lineHeightUnits.Desktop')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('lineHeight.Tablet')->label('Tablet')->numeric(),
                                                    Select::make('lineHeightUnits.Tablet')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('lineHeight.Mobile')->label('Mobile')->numeric(),
                                                    Select::make('lineHeightUnits.Mobile')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                    ]),
                            Fieldset::make('Letter Spacing')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 3,
                                    ])
                                    ->schema([
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('letterSpacing.Desktop')->label('Desktop')->numeric(),
                                                    Select::make('letterSpacingUnit.Desktop')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('letterSpacing.Tablet')->label('Tablet')->numeric(),
                                                    Select::make('letterSpacingUnit.Tablet')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                        Section::make()
                                                ->schema([
                                                    TextInput::make('letterSpacing.Mobile')->label('Mobile')->numeric(),
                                                    Select::make('letterSpacingUnit.Mobile')->options(['px'=>'px','em'=>'em','%'=>'%'])->label('Units')->default('px'),
                                                ])
                                                ->columns(2),
                                    ]),
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
