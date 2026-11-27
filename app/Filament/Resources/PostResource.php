<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Grower tips';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, Forms\Set $set, $record) => $record ? null : $set('slug', \Illuminate\Support\Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('category')->options(['Plant care' => 'Plant care', 'Tree planting' => 'Tree planting', 'Seedlings' => 'Seedlings', 'News' => 'News']),
            Forms\Components\DateTimePicker::make('published_at')->label('Publish on')->helperText('Leave empty to keep as a draft.'),
            Forms\Components\TextInput::make('excerpt')->columnSpanFull(),
            \App\Filament\Forms\MediaPicker::attach(Forms\Components\FileUpload::make('image_path')->label('Photo')->image()->disk('public')->directory('posts')->columnSpanFull()),
            Forms\Components\RichEditor::make('body')->columnSpanFull()->disableToolbarButtons(['attachFiles', 'codeBlock']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('published_at')->label('Published')->date('j M Y')->placeholder('Draft'),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePosts::route('/')];
    }
}
