<?php

namespace App\Repositories\Product;

use App\Models\Inventory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class InventoryRepository extends BaseRepository
{
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    public function findLowStock(int $threshold = 10): Collection
    {
        return $this->model->where('quantity', '<=', $threshold)->get();
    }

    public function updateQuantity(int $variantId, int $quantity): bool
    {
        $inventory = $this->model->where('product_variant_id', $variantId)->first();
        if ($inventory) {
            $inventory->quantity = $quantity;
            $inventory->updated_at = now();
            return $inventory->save();
        }
        return false;
    }

    public function deductQuantity(int $variantId, int $quantity): bool
    {
        $inventory = $this->model->where('product_variant_id', $variantId)->first();
        if ($inventory && $inventory->quantity >= $quantity) {
            $inventory->quantity -= $quantity;
            $inventory->updated_at = now();
            return $inventory->save();
        }
        return false;
    }

    public function restoreQuantity(int $variantId, int $quantity): bool
    {
        $inventory = $this->model->where('product_variant_id', $variantId)->first();
        if ($inventory) {
            $inventory->quantity += $quantity;
            $inventory->updated_at = now();
            return $inventory->save();
        }
        return false;
    }
}