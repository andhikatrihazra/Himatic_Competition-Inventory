<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboundProduct extends Model
{
    protected $fillable = [
        'inbound_product_number',
        'quantity_total',
        'total',
        'date',
    ];

    public function PivotInboundProduct()
    {
        return $this->hasMany(PivotInboundProduct::class);
    }
}
