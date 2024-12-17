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
        // Total Penjualan Hari Ini (Sales)
        $totalSalesToday = PivotOutboundProduct::join('outbound_products', 'pivot_outbound_products.outbound_product_id', '=', 'outbound_products.id')
            ->whereDate('outbound_products.created_at', now()->toDateString()) // Ensure 'created_at' is qualified
            ->sum(DB::raw('pivot_outbound_products.product_quantity * pivot_outbound_products.product_selling_price'));

        // Total Harga Modal Hari Ini (Purchase Price)
        $totalPurchasePriceToday = PivotOutboundProduct::join('outbound_products', 'pivot_outbound_products.outbound_product_id', '=', 'outbound_products.id')
            ->join('products', 'pivot_outbound_products.product_id', '=', 'products.id') // Join with 'products' table to access 'purchase_price'
            ->whereDate('outbound_products.created_at', now()->toDateString()) // Filter outbound products by today
            ->sum(DB::raw('pivot_outbound_products.product_quantity * products.purchase_price'));

        // Keuntungan Hari Ini (Profit)
        $profitToday = $totalSalesToday - $totalPurchasePriceToday;

        // Produk Terlaris Hari Ini & Quantity Terjual
        $bestSellingProduct = PivotOutboundProduct::select('products.name', DB::raw('SUM(pivot_outbound_products.product_quantity) as total_sold'))
            ->join('products', 'pivot_outbound_products.product_id', '=', 'products.id')
            ->join('outbound_products', 'pivot_outbound_products.outbound_product_id', '=', 'outbound_products.id')
            ->whereDate('outbound_products.created_at', now()->toDateString()) // Filter outbound products by today
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->first();

        // Best-selling product name and quantity sold
        $bestSellingProductName = $bestSellingProduct ? $bestSellingProduct->name : 'No sales yet';
        $bestSellingQuantity = $bestSellingProduct ? $bestSellingProduct->total_sold : 0;

        return [
            Stat::make('Total Penjualan Hari Ini', 'Rp ' . number_format($totalSalesToday, 0, ',', '.'))
                ->description('Total sales for today')
                ->color('success'),

            Stat::make('Produk Terlaris Hari Ini', $bestSellingProductName . ' (' . $bestSellingQuantity . ' terjual)')
                ->description('Best-selling product and quantity sold for today')
                ->color('primary'),

            Stat::make('Keuntungan Hari Ini', 'Rp ' . number_format($profitToday, 0, ',', '.'))
                ->description('Profit for today')
                ->color('danger'),
        ];
    }
}
