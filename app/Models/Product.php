<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'purchase_price',
        'selling_price',
        'stock',
        'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function PivotOutboundProduct()
    {
        return $this->hasMany(PivotOutboundProduct::class);
    }
}
