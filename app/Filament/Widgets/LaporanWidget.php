<?php

namespace App\Filament\Widgets;

use App\Models\OutboundProduct;
use App\Models\Product;
use App\Models\PivotOutboundProduct;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class LaporanWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $totalProfit = OutboundProduct::query()->sum('profits');

        $totalProfitThisMonth = OutboundProduct::query()
            ->whereMonth('date', Carbon::now()->month) 
            ->whereYear('date', Carbon::now()->year)   
            ->sum('profits');

        $produkTerlaris = PivotOutboundProduct::query()
            ->join('products', 'pivot_outbound_products.product_id', '=', 'products.id') // Menggunakan product_id yang benar
            ->join('outbound_products', 'pivot_outbound_products.outbound_product_id', '=', 'outbound_products.id')
            ->select('products.name as product_name')
            ->selectRaw('SUM(pivot_outbound_products.product_quantity) as total_terjual') // Menggunakan product_quantity yang benar
            ->groupBy('pivot_outbound_products.product_id', 'products.name')
            ->orderByDesc('total_terjual')
            ->first(); // Ambil produk terlaris pertama

        return [
            Card::make('Total Keuntungan', 'Rp ' . number_format($totalProfit, 0, ',', '.')),
            Card::make('Keuntungan Bulan Ini', 'Rp ' . number_format($totalProfitThisMonth, 0, ',', '.')),
            Card::make(
                'Produk Terlaris',
                $produkTerlaris
                    ? "{$produkTerlaris->product_name} ({$produkTerlaris->total_terjual} terjual)"
                    : 'Belum ada data'
            ),
        ];
    }
}
