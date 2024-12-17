<?php

namespace App\Services;

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use App\Models\OutboundProduct;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PrinterService
{
    protected $printer;

    private function initializePrinter()
    {
        try {
            Log::info('Mencoba menginisialisasi printer');
            $connector = new WindowsPrintConnector('POS-58'); 
            $this->printer = new Printer($connector);
            Log::info('Printer berhasil diinisialisasi');
            return true;
        } catch (\Exception $e) {
            Log::error('Gagal inisialisasi printer: ' . $e->getMessage());
            throw $e;
        }
    }

    private function printReceiptContent($invoice, bool $isFirst = true)
    {
        try {
            Log::info('Mulai mencetak nota: ' . ($isFirst ? 'ASLI' : 'COPY'));
            
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("TOKO KITA\n");
            $this->printer->text("Jl. Jalan Kemana-mana No. 123\n");
            $this->printer->text("Telp: 08123456789\n");
            $this->printer->text("--------------------------------\n");

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Tanggal: " . $invoice->created_at->format('d/m/Y') . "\n");
            $this->printer->text("No: " . $invoice->outbound_product_number . "\n");
            $this->printer->text("--------------------------------\n");

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text($isFirst ? "STRUK CUSTOMER\n" : "STRUK TOKO\n");
            $this->printer->text("--------------------------------\n");

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $totalItems = 0;
            $grandTotal = 0;

            foreach ($invoice->PivotOutboundProduct as $item) {
                $productName = $item->product->name ?? 'Produk Tidak Diketahui';
                $quantity = $item->product_quantity;
                $price = $item->product_selling_price;
                $subtotal = $item->subtotal;

                $totalItems += $quantity;
                $grandTotal += $subtotal;

                $this->printer->text($productName . "\n");
                $this->printer->text("  {$quantity} x " . number_format($price, 0, ',', '.') . " = " . number_format($subtotal, 0, ',', '.') . "\n");
            }

            $this->printer->text("--------------------------------\n");
            $this->printer->text("Total Items: {$totalItems}\n");
            $this->printer->text("Total Harga: Rp. " . number_format($grandTotal, 0, ',', '.') . "\n");

            if (!$isFirst) {
                $todayTotal = $this->calculateTodayTotal();
                $this->printer->text("--------------------------------\n");
                $this->printer->text("Total Penjualan Hari Ini:\n");
                $this->printer->text("Rp. " . number_format($todayTotal, 0, ',', '.') . "\n");
            }

            $this->printer->text("--------------------------------\n");
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("Terima Kasih Atas Kunjungan Anda\n");
            $this->printer->text("\n\n");

            $this->printer->cut();
            Log::info('Berhasil mencetak nota: ' . ($isFirst ? 'ASLI' : 'COPY'));
        } catch (\Exception $e) {
            Log::error('Error saat mencetak konten: ' . $e->getMessage());
            throw $e;
        }
    }

    private function calculateTodayTotal()
    {
        $today = Carbon::today();
        return OutboundProduct::whereDate('created_at', $today)
            ->sum('total'); 
    }

    public function printFirstReceipt($invoice)
    {
        try {
            $this->initializePrinter();
            $this->printReceiptContent($invoice, true);
            $this->printer->close();
            Log::info('Nota customer berhasil dicetak');
            return true;
        } catch (\Exception $e) {
            Log::error('Error saat mencetak nota customer: ' . $e->getMessage());
            throw $e;
        }
    }

    public function printSecondReceipt($invoice)
    {
        try {
            $this->initializePrinter();
            $this->printReceiptContent($invoice, false);
            $this->printer->close();
            Log::info('Nota toko berhasil dicetak');
            return true;
        } catch (\Exception $e) {
            Log::error('Error saat mencetak nota toko: ' . $e->getMessage());
            throw $e;
        }
    }
}
