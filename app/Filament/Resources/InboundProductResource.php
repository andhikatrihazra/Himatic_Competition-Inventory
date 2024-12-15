<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\InboundProduct;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InboundProductResource\Pages;
use App\Filament\Resources\InboundProductResource\RelationManagers;

class InboundProductResource extends Resource
{
    protected static ?string $model = InboundProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?string $navigationGroup = 'In | Out Product';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $products = Product::all();

        return $form
        ->schema([
            Section::make('Inbound Product Details')
                ->schema([
                    Repeater::make('PivotInboundProduct')
                    ->relationship('PivotInboundProduct')
                    ->columns(5)
                    ->schema([
                        Select::make('product_id')
                        ->label('Product')
                        ->options(
                            $products->pluck('name', 'id')
                        )
                        ->reactive() 
                        ->afterStateUpdated(fn ($state, callable $set) => 
                            $set('stock', optional($products->firstWhere('id', $state))->stock)
                        )
                        ->searchable()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $product = Product::find($state);
                            $pricePerUnit = $product->purchase_price;
                            $quantity = $get('product_quantity') ?? 1;
                            
                            // Add stock availability validation
                            // $set('max_quantity', $product->stock);
                            
                            $set('product_purchase_price', $pricePerUnit);
                            $set('subtotal', $pricePerUnit * $quantity);
                        }),
                        
                        TextInput::make('stock')
                        ->label('Stock')
                        ->readonly(),

                        TextInput::make('product_quantity')
                            ->label('Quantity')
                            ->integer()
                            ->default(1)
                            ->required()
                            // ->maxValue(fn($get) => $get('max_quantity') ?? 0)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $pricePerUnit = $get('product_purchase_price');
                                $set('subtotal', $pricePerUnit * $state);
                            }),

                        TextInput::make('product_purchase_price')
                            ->label('Purchase Price')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),

                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),
                    ])
                    ->live()
                    ->afterStateUpdated(function ($get, $set) {
                        self::updateTotals($get, $set);
                    })
                    ->reorderable(false),
                ]),

            Section::make('Total')
                ->schema([
                    TextInput::make('inbound_product_number')
                        ->label('Inbound Number')
                        ->default(function() {
                            return self::generateInboundNumber();
                        })
                        ->readOnly()
                        ->required(),

                    TextInput::make('quantity_total')
                        ->label('Total Quantity')
                        ->numeric()
                        ->readOnly()
                        ->default(0),

                    TextInput::make('total')
                        ->numeric()
                        ->readOnly()
                        ->prefix('Rp'),

                    TextInput::make('date')
                        ->type('date')
                        ->required()
                        ->default(now()->toDateString()),
                ]),
        ]);
    }

    public static function updateTotals($get, $set): void
    {
        $selectedProducts = collect($get('PivotInboundProduct'))->filter(fn($item) => 
            !empty($item['product_id']) && !empty($item['product_quantity'])
        );
        
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('purchase_price', 'id');

        $total = $selectedProducts->reduce(function ($total, $product) use ($prices) {
            return $total + ($prices[$product['product_id']] * $product['product_quantity']);
        }, 0);

        $totalQuantity = $selectedProducts->sum('product_quantity');
        
        $set('quantity_total', $totalQuantity);
        $set('total', $total);
    }

    public static function generateInboundNumber()
    {
        $today = now()->toDateString();

        $allInboundProducts = InboundProduct::all(); 
        $totalInboundProducts = $allInboundProducts->count();

        $romanTotalInboundProducts = $totalInboundProducts === 0 ? 'I' : self::toRoman($totalInboundProducts + 1);

        $todayInboundProducts = InboundProduct::whereDate('created_at', $today)->get();
        $lastInboundProduct = $todayInboundProducts->last();
        $lastNumber = $lastInboundProduct ? (int)substr($lastInboundProduct->inbound_product_number, -3) : 0;

        return $romanTotalInboundProducts . '-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    public static function toRoman($number)
    {
        $map = [
            1000 => 'M',
            900 => 'CM',
            500 => 'D',
            400 => 'CD',
            100 => 'C',
            90 => 'XC',
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I'
        ];

        $roman = '';
        foreach ($map as $value => $symbol) {
            while ($number >= $value) {
                $roman .= $symbol;
                $number -= $value;
            }
        }

        return $roman;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label('Date')->sortable(),
                TextColumn::make('inbound_product_number')->label('Inbound Number')->sortable(),
                TextColumn::make('quantity_total')->label('Total Quantity')->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(function ($state) {
                        return 'Rp ' . number_format((float)$state, 0, ',', '.');
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInboundProducts::route('/'),
            'create' => Pages\CreateInboundProduct::route('/create'),
            'edit' => Pages\EditInboundProduct::route('/{record}/edit'),
        ];
    }
}