<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OutboundProduct;
use Illuminate\Http\Request;

class ExportPDFController extends Controller
{
    public function export(Request $request)
{
    $query = OutboundProduct::query()
        ->select('date')
        ->selectRaw('SUM(total) as pendapatan_kotor')
        ->selectRaw('SUM(total_purchase_price) as total_harga_modal')
        ->selectRaw('SUM(quantity_total) as total_item')
        ->selectRaw('SUM(profits) as pendapatan_bersih')
        ->groupBy('date')
        ->orderBy('date', 'asc'); 

    $query->whereMonth('date', '=', now()->month)
        ->whereYear('date', '=', now()->year);

    $data = $query->get();

    $totals = [
        'pendapatan_kotor' => $data->sum('pendapatan_kotor'),
        'total_harga_modal' => $data->sum('total_harga_modal'),
        'pendapatan_bersih' => $data->sum('pendapatan_bersih'),
    ];

    $title = $request->get('title', 'Laporan Penjualan Bulan Ini - Toko Bu Budi');

    $pdf = Pdf::loadView('filament.pages.laporan-pdf', compact('data', 'totals', 'title'));

    return $pdf->download($title . '.pdf');
}

}
