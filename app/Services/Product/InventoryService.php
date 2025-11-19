<?php

namespace App\Services\Product;

use App\Repositories\InventoryRepository;
use Illuminate\Database\Eloquent\Collection;

class InventoryService
{
    protected InventoryRepository $inventoryRepository;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    public function getLowStockAlerts(int $threshold = 10): Collection
    {
        return $this->inventoryRepository->findLowStock($threshold);
    }

    public function updateInventory(int $variantId, int $quantity): bool
    {
        return $this->inventoryRepository->updateQuantity($variantId, $quantity);
    }

    public function checkAvailability(int $variantId, int $quantity): bool
    {
        $inventory = $this->inventoryRepository->find($variantId);
        return $inventory && $inventory->quantity >= $quantity;
    }
}