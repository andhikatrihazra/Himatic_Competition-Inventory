<?php

namespace App\Http\Controllers;

use App\Models\OutboundProduct;
use App\Services\PrinterService;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use App\Filament\Resources\OutboundProductResource;

class PrintController extends Controller
{
    public function printSecond($invoiceId)
    {
        try {
            Log::info('Memulai pencetakan nota kedua');
            
            $invoice = OutboundProduct::findOrFail($invoiceId);
            
            $printService = new PrinterService();
            $printService->printSecondReceipt($invoice);
            
            Log::info('Nota kedua berhasil dicetak');
            
            Notification::make('success-print')
                ->title('Nota kedua telah dicetak')
                ->success()
                ->persistent()
                ->send();
            return redirect('/admin/outbound-products');
        } catch (\Exception $e) {
            Log::error('Error saat mencetak nota kedua: ' . $e->getMessage());
            
            Notification::make('error-print')
                ->title('Gagal mencetak nota kedua')
                ->danger()
                ->body($e->getMessage())
                ->persistent()
                ->send();
            return redirect('/admin/outbound-products');


        }
    }
}