<?php

namespace App\Filament\Resources\OutboundProductResource\Pages;

use Illuminate\Support\Facades\DB;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\OutboundProductResource;
use App\Models\Product;


class CreateOutboundProduct extends CreateRecord
{
    protected static string $resource = OutboundProductResource::class;

    protected function afterCreate(): void
    {
        // Start a database transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Get the created outbound product
            $outboundProduct = $this->record;

            // Iterate through the pivot outbound products
            foreach ($outboundProduct->PivotOutboundProduct as $pivotProduct) {
                // Find the corresponding product
                $product = Product::findOrFail($pivotProduct->product_id);

                // Reduce the stock
                $product->stock -= $pivotProduct->product_quantity;

                // Ensure stock doesn't go negative
                $product->stock = max(0, $product->stock);

                // Save the updated product
                $product->save();
            }

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Optionally, you can log the error
            \Log::error('Error reducing product stock: ' . $e->getMessage());

            // Throw the exception to prevent record creation
            throw $e;
        }
    }
}