<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PivotOutboundProduct extends Model
{
    protected $fillable = [
        'outbound_product_id',
        'product_id',
        'product_quantity',
        'product_selling_price',
        'subtotal',
        'stock'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function outbound_product()
    {
        return $this->belongsTo(OutboundProduct::class);
    }
}

