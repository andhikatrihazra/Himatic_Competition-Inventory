<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutboundProductResource\Widgets\OutboundProductWidget;
use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\OutboundProduct;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Filament\Resources\OutboundProductResource\Pages;

class OutboundProductResource extends Resource
{
    protected static ?string $model = OutboundProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';
    protected static ?string $navigationGroup = 'In | Out Product';
    protected static ?string $navigationLabel = 'Barang Keluar';
    public static function form(Form $form): Form
    {
        $products = Product::all();

        return $form
        ->schema([
            Section::make('Outbound Product Details')
                ->schema([
                    Repeater::make('PivotOutboundProduct')
                    ->relationship('PivotOutboundProduct')
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
                                $pricePerUnit = $product->selling_price;
                                $quantity = $get('product_quantity') ?? 1;
                                
                                $set('max_quantity', $product->stock);
                                
                                $set('product_selling_price', $pricePerUnit);
                                $set('subtotal', $pricePerUnit * $quantity);
                            }),
                        
                        TextInput::make('stock')
                        ->label('Stock')
                        ->readonly(),
                        // ->disabled()
                        // ->hidden(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord || $livewire instanceof \Filament\Resources\Pages\ViewRecord),

                        TextInput::make('product_quantity')
                            ->label('Quantity')
                            ->integer()
                            ->default(1)
                            ->required()
                            ->maxValue(fn($get) => $get('max_quantity') ?? 0)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $pricePerUnit = $get('product_selling_price');
                                $set('subtotal', $pricePerUnit * $state);
                            }),

                            TextInput::make('product_selling_price')
                                ->label('Selling Price')
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
                    TextInput::make('outbound_product_number')
                        ->label('Outbound Number')
                        ->default(function() {
                            return self::generateOutboundNumber();
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

                    TextInput::make('total_purchase_price')
                        ->label('Total Harga Modal')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly(),
            
                    TextInput::make('profits')
                        ->label('Profit')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly(),

                    TextInput::make('date')
                        ->type('date')
                        ->required()
                        ->readOnly()
                        ->default(now()->toDateString()),
                ]),
                
        ]);
    }
    public static function updateTotals($get, $set): void
{
    $selectedProducts = collect($get('PivotOutboundProduct'))
        ->filter(fn($item) => 
            !empty($item['product_id']) && 
            !empty($item['product_quantity'])
        );

    $products = Product::find($selectedProducts->pluck('product_id'));

    // Hitung total penjualan (revenue)
    $total = $selectedProducts->reduce(function ($total, $product) use ($products) {
        $productModel = $products->firstWhere('id', $product['product_id']);
        return $total + ($productModel->selling_price * $product['product_quantity']);
    }, 0);

    // Hitung total harga modal (purchase price)
    $totalPurchasePrice = $selectedProducts->reduce(function ($total, $product) use ($products) {
        $productModel = $products->firstWhere('id', $product['product_id']);
        return $total + ($productModel->purchase_price * $product['product_quantity']);
    }, 0);

    // Hitung profits
    $profits = $total - $totalPurchasePrice;

    $totalQuantity = $selectedProducts->sum('product_quantity');
    
    $set('quantity_total', $totalQuantity);
    $set('total', $total);
    $set('total_purchase_price', $totalPurchasePrice);
    $set('profits', $profits);
    }

    public static function generateOutboundNumber()
    {
        $today = now()->toDateString();

        $allOutboundProducts = OutboundProduct::all(); 
        $totalOutboundProducts = $allOutboundProducts->count();

        $romanTotalOutboundProducts = $totalOutboundProducts === 0 ? 'I' : self::toRoman($totalOutboundProducts + 1);

        $todayOutboundProducts = OutboundProduct::whereDate('created_at', $today)->get();
        $lastOutboundProduct = $todayOutboundProducts->last();
        $lastNumber = $lastOutboundProduct ? (int)substr($lastOutboundProduct->outbound_product_number, -3) : 0;

        return $romanTotalOutboundProducts . '-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
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
                TextColumn::make('outbound_product_number')->label('Outbound Number')->sortable(),
                TextColumn::make('quantity_total')
                ->label('Total Quantity')
                ->sortable()
                ->summarize(Sum::make()
                ->prefix('Total : ')
                ),  
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(function ($state) {
                        return 'Rp ' . number_format((float)$state, 0, ',', '.');
                    })
                    ->sortable()
                    ->summarize(Sum::make()->money('idr')
                    ->prefix('Total : ')
                    ),  
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('Rentang Tanggal')
                ->form([
                    DatePicker::make('tanggal_awal')->label('Tanggal Awal'),
                    DatePicker::make('tanggal_akhir')->label('Tanggal Akhir'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['tanggal_awal'] ?? null, 
                            fn ($query, $date) => $query->where('date', '>=', $date))
                        ->when($data['tanggal_akhir'] ?? null, 
                            fn ($query, $date) => $query->where('date', '<=', $date));
                }),
            ])
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
            'index' => Pages\ListOutboundProducts::route('/'),
            'create' => Pages\CreateOutboundProduct::route('/create'),
            'edit' => Pages\EditOutboundProduct::route('/{record}/edit'),
            'view' => Pages\ViewOutboundProduct::route('/{record}'),
        ];
    }
}