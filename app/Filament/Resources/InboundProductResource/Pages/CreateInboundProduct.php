<?php

namespace App\Filament\Resources\InboundProductResource\Pages;

use Illuminate\Support\Facades\DB;
use App\Filament\Resources\InboundProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Product;


class CreateInboundProduct extends CreateRecord
{
    protected static string $resource = InboundProductResource::class;

    protected function afterCreate(): void
    {
        // Start a database transaction to ensure data integrity
        DB::beginTransaction();
    
        try {
            // Get the created inbound product
            $inboundProduct = $this->record;
    
            // Iterate through the pivot inbound products (assuming the relation is similar)
            foreach ($inboundProduct->PivotInboundProduct as $pivotProduct) {
                // Find the corresponding product
                $product = Product::findOrFail($pivotProduct->product_id);
    
                // Increase the stock based on the inbound quantity
                $product->stock += $pivotProduct->product_quantity;
    
                // Save the updated product stock
                $product->save();
            }
    
            // Commit the transaction to persist the changes
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
    
            // Log the error for debugging purposes
            \Log::error('Error updating product stock for inbound: ' . $e->getMessage());
    
            // Rethrow the exception to prevent record creation
            throw $e;
        }
    }
}
