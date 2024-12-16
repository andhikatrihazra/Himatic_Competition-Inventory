<?php

namespace App\Filament\Pages;

use App\Models\OutboundProduct;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class Laporan extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $title = 'Laporan Penjualan';
    protected static string $view = 'filament.pages.laporan';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // This method is used to define the query for the table data

    protected function getTableQuery(): Builder
{
    return OutboundProduct::query()
        ->select('date')
        ->selectRaw('SUM(total) as pendapatan_kotor')
        ->selectRaw('SUM(total_purchase_price) as total_harga_modal')
        ->selectRaw('SUM(quantity_total) as total_item')
        ->selectRaw('SUM(profits) as laba')
        ->groupBy('date')
        ->orderBy('date', 'desc');
    }

    // This method defines the columns to display in the table
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('date')
                ->label('Tanggal')
                // ->sortable()
                ->date('d-m-Y'),

            TextColumn::make('total_item')
                ->label('Jumlah Total Item Terjual')
                ->alignCenter(),
                // ->sortable(),

                TextColumn::make('total_harga_modal')
                ->label('Total Harga Modal')
                ->money('idr')
                ->formatStateUsing(function ($state) {
                    return 'Rp ' . number_format($state, 0, ',', '.');
                })
                ->alignEnd(),
    
            TextColumn::make('laba')
                ->label('Laba')
                ->alignEnd()
                ->formatStateUsing(function ($state) {
                    return 'Rp ' . number_format($state ?: 0, 0, ',', '.');
                }),
                // ->sortable(),
        ];
    }

    // Filter form for the table
    protected function getTableFilters(): array
    {
        return [
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
        ];
    }

    // Custom record key
    public function getTableRecordKey($record): string
    {
        return $record->date ? (string) $record->date : uniqid();
    }
}
