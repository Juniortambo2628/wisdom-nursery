<?php

namespace App\Filament\Resources;

use App\Enums\Availability;
use App\Enums\SoldAs;
use App\Filament\Resources\PlantResource\Pages;
use App\Models\Plant;
use App\Support\QrLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PlantResource extends Resource
{
    protected static ?string $model = Plant::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Plants';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Plant')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(120)
                    ->live(onBlur: true)->afterStateUpdated(fn ($state, Forms\Set $set, ?Plant $record) => $record ? null : $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->helperText('Part of the web address; avoid changing it once QR labels are printed.'),
                Forms\Components\TextInput::make('botanical_name'),
                Forms\Components\Select::make('category_id')->relationship('category', 'name')->required(),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Price and stock')->columns(3)->schema([
                Forms\Components\TextInput::make('price')->label('Price (KSh)')->numeric()->minValue(1)->required(),
                Forms\Components\Select::make('sold_as')->options(collect(SoldAs::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))->required(),
                Forms\Components\TextInput::make('stock_qty')->label('In stock')->numeric()->minValue(0)->required(),
                Forms\Components\Select::make('availability')->options(collect(Availability::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))->required()->live(),
                Forms\Components\DatePicker::make('ready_on')->visible(fn (Get $get) => $get('availability') === Availability::ReadyOn->value)->required(fn (Get $get) => $get('availability') === Availability::ReadyOn->value),
                Forms\Components\TextInput::make('bulk_min_qty')->label('Bulk from (quote)')->numeric()->minValue(1)->helperText('Shows "Bulk (N+)" and "Add to quote".'),
                Forms\Components\TextInput::make('set_size')->label('Set of')->numeric()->minValue(2),
                Forms\Components\TextInput::make('set_price')->label('Set price (KSh)')->numeric()->minValue(1)->requiredWith('set_size'),
            ]),
            Forms\Components\Section::make('Care facts')->columns(4)->collapsible()->schema([
                Forms\Components\TextInput::make('light'),
                Forms\Components\TextInput::make('water'),
                Forms\Components\TextInput::make('pot_size'),
                Forms\Components\TextInput::make('grows_to'),
            ]),
            Forms\Components\Section::make('Photos')->schema([
                Forms\Components\Repeater::make('images')->relationship()->orderColumn('sort_order')->grid(3)->defaultItems(0)
                    ->schema([
                        \App\Filament\Forms\MediaPicker::attach(Forms\Components\FileUpload::make('path')->hiddenLabel()->image()->disk('public')->directory('plants')->maxSize(12288)->required()),
                        Forms\Components\TextInput::make('alt')->placeholder('What the photo shows'),
                    ]),
            ]),
            Forms\Components\Section::make('Show on the site')->columns(3)->schema([
                Forms\Components\Toggle::make('is_active')->label('Visible')->default(true),
                Forms\Components\Toggle::make('is_featured')->label('Popular this season'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Select::make('pairings')->relationship('pairings', 'name')->multiple()->preload()->label('Goes well with')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('primaryImage.path')->disk('public')->label('')->square()->size(40),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->description(fn (Plant $p) => $p->category->name),
                // Quick edits straight from the list (handoff: quick price and stock edits).
                Tables\Columns\TextInputColumn::make('price')->label('Price (KSh)')->type('number')->rules(['required', 'integer', 'min:1'])->width('9rem'),
                Tables\Columns\TextInputColumn::make('stock_qty')->label('In stock')->type('number')->rules(['required', 'integer', 'min:0'])->width('8rem'),
                Tables\Columns\SelectColumn::make('availability')->options(collect(Availability::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))->selectablePlaceholder(false),
                Tables\Columns\ToggleColumn::make('is_active')->label('Visible'),
                Tables\Columns\ToggleColumn::make('is_featured')->label('Popular')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
                Tables\Filters\Filter::make('low_stock')->label('Low stock (under 5)')->query(fn ($query) => $query->where('stock_qty', '<', 5)),
                Tables\Filters\TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\Action::make('view_on_site')->icon('heroicon-o-arrow-top-right-on-square')->iconButton()->color('gray')
                    ->url(fn (Plant $p) => route('plants.show', $p))->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('qr_labels')->label('Print QR labels')->icon('heroicon-o-qr-code')
                    ->action(fn (Collection $records) => response()->streamDownload(
                        fn () => print(QrLabels::pdf($records->sortBy('name')->values())),
                        'wisdom-nursery-qr-labels.pdf'
                    ))->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('hide')->label('Hide from site')->icon('heroicon-o-eye-slash')->color('gray')
                    ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlants::route('/'),
            'create' => Pages\CreatePlant::route('/create'),
            'edit' => Pages\EditPlant::route('/{record}/edit'),
        ];
    }
}
