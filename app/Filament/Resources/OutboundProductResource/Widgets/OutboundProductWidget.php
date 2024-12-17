<?php
namespace App\Filament\Resources\OutboundProductResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\PivotOutboundProduct;
use Illuminate\Support\Facades\DB;

class OutboundProductWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Total Penjualan Hari Ini
        $totalSalesToday = PivotOutboundProduct::join('outbound_products', 'pivot_outbound_products.outbound_product_id', '=', 'outbound_products.id')
            ->whereDate('outbound_products.created_at', now()->toDateString()) // Ensure 'created_at' is qualified
            ->sum(DB::raw('pivot_outbound_products.product_quantity * pivot_outbound_products.product_selling_price'));

        // Produk Terlaris Hari Ini
        $bestSellingProduct = PivotOutboundProduct::select('products.name', DB::raw('SUM(pivot_outbound_products.product_quantity) as total_sold'))
            ->join('products', 'pivot_outbound_products.product_id', '=', 'products.id')
            ->join('outbound_products', 'pivot_outbound_products.outbound_product_id', '=', 'outbound_products.id')
            ->whereDate('outbound_products.created_at', now()->toDateString()) // Filter outbound products by today
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->first();

        return [
            Stat::make('Total Penjualan Hari Ini', 'Rp ' . number_format($totalSalesToday, 0, ',', '.'))
                ->description('Total sales for today')
                ->color('success'),

            Stat::make('Produk Terlaris Hari Ini', $bestSellingProduct ? $bestSellingProduct->name : 'No sales yet')
                ->description('Best-selling product for today')
                ->color('primary'),
        ];
    }
}
