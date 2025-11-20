<?php

namespace App\Models;

use App\Observers\Product\ProductObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ProductObserver::class])]
class Product extends Model
{
    protected $fillable = [
        'vendor_id',
        'category',
        'name',
        'description',
        'base_price',
        'sku',
        'is_active'
    ];
    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Inventory belongs to a ProductVariant
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
