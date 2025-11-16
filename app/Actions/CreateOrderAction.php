<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function execute(array $orderData, array $items, int $userId): Order
    {
        return $this->orderService->createOrder($orderData, $items, $userId);
    }
}