<?php

namespace App\Filament\Resources\OutboundProductResource\Pages;

use App\Models\Product;
use App\Services\PrinterService;
use Filament\Notifications\Actions\Action;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\OutboundProductResource;

class CreateOutboundProduct extends CreateRecord
{
    protected static string $resource = OutboundProductResource::class;

    protected function afterCreate(): void
    {
        DB::beginTransaction();

        try {
            $outboundProduct = $this->record;

            foreach ($outboundProduct->PivotOutboundProduct as $pivotProduct) {
                $product = Product::findOrFail($pivotProduct->product_id);

                $product->stock -= $pivotProduct->product_quantity;

                $product->stock = max(0, $product->stock);

                $product->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error reducing product stock: ' . $e->getMessage());

            throw $e;
        }

        $invoice = $this->record;
        $printService = new PrinterService();

        try {
            $printService->printFirstReceipt($invoice);

            Notification::make('print-second')
                ->title('Nota pertama telah dicetak')
                ->body('Silakan sobek nota pertama, kemudian klik tombol "Cetak Nota Kedua"')
                ->persistent() 
                ->actions([
                    Action::make('printSecond')
                        ->label('Cetak Nota Kedua')
                        ->button()
                        ->color('primary')
                        ->url(route('print.second', ['invoice' => $invoice->id]))
                ])
                ->success()
                ->persistent() 
                ->send();

        } catch (\Exception $e) {
            Log::error('Error saat mencetak nota pertama: ' . $e->getMessage());
            
            Notification::make('error')
                ->title('Gagal mencetak struk')
                ->danger()
                ->body($e->getMessage())
                ->persistent() 
                ->send();
        }
        
    }
}