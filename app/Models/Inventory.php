<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $fillable = [
        'product_variant_id',
        'quantity',
        'low_stock_threshold'
    ];

    /**
     * Inventory belongs to a ProductVariant
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Shortcut to product through variant
     */
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            ProductVariant::class,
            'id', // ProductVariant primary key
            'id', // Product primary key
            'product_variant_id', // Local key on Inventory
            'product_id' // Foreign key on ProductVariant
        );
    }
}
