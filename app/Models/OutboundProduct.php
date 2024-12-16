<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundProduct extends Model
{
    protected $fillable = [
        'outbound_product_number',
        'quantity_total',
        'total',
        'total_purchase_price',
        'profits',
        'date',
    ];

    public function PivotOutboundProduct()
    {
        return $this->hasMany(PivotOutboundProduct::class);
    }
}
