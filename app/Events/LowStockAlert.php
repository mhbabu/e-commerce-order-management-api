<?php

namespace App\Events;

use App\Models\ProductVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert
{
    use Dispatchable, SerializesModels;

    public ProductVariant $variant;
    public int $currentStock;

    public function __construct(ProductVariant $variant, int $currentStock)
    {
        $this->variant      = $variant;
        $this->currentStock = $currentStock;
    }
}