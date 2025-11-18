<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attributes',
        'price_modifier',
        'sku',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array'
    ];

    /**
     * Variant belongs to a Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Variant has one Inventory
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'product_variant_id');
    }
}
