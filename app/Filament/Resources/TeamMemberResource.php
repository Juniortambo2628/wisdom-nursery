<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages;
use App\Models\TeamMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Team';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('role')->required(),
            Forms\Components\FileUpload::make('photo_path')->label('Portrait')->image()->imageEditor()->disk('public')->directory('team'),
            Forms\Components\Toggle::make('is_active')->label('Show on site')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')->disk('public')->circular()->label(''),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Shown'),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageTeamMembers::route('/')];
    }
}
