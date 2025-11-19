<?php

namespace App\Actions;

use App\Services\Product\InventoryService;

class UpdateInventoryAction
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function execute(int $variantId, int $quantity): bool
    {
        return $this->inventoryService->updateInventory($variantId, $quantity);
    }
}