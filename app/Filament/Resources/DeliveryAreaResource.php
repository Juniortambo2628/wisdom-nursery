<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryAreaResource\Pages;
use App\Models\DeliveryArea;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveryAreaResource extends Resource
{
    protected static ?string $model = DeliveryArea::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Plants';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('fee')->label('Fee (KSh)')->numeric()->minValue(0)->required(),
            Forms\Components\TextInput::make('eta')->label('Delivery time')->placeholder('e.g. Next day'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('fee')->formatStateUsing(fn ($state) => ksh($state)),
                Tables\Columns\TextColumn::make('eta')->placeholder('-'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageDeliveryAreas::route('/')];
    }
}
