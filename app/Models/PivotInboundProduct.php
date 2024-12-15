<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PivotInboundProduct extends Model
{
    protected $fillable = [
        'inbound_product_id',
        'product_id',
        'product_quantity',
        'product_purchase_price',
        'subtotal',
        'stock'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inbound_product()
    {
        return $this->belongsTo(InboundProduct::class);
    }
}
