<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\OrderRepository;
use App\Repositories\InventoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected OrderRepository $orderRepository;
    protected InventoryRepository $inventoryRepository;

    public function __construct(OrderRepository $orderRepository, InventoryRepository $inventoryRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function createOrder(array $orderData, array $items, int $userId): Order
    {
        return DB::transaction(function () use ($orderData, $items, $userId) {
            // Check inventory availability
            foreach ($items as $item) {
                $inventory = $this->inventoryRepository->find($item['product_variant_id']);
                if (!$inventory || $inventory->quantity < $item['quantity']) {
                    throw new \Exception('Insufficient inventory for product variant ' . $item['product_variant_id']);
                }
            }

            $total = $this->calculateTotal($items);
            $order = $this->orderRepository->create(array_merge($orderData, [
                'user_id' => $userId,
                'total_amount' => $total,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            ]));

            foreach ($items as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
                // Deduct inventory
                $this->inventoryRepository->deductQuantity($item['product_variant_id'], $item['quantity']);
            }

            return $order;
        });
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        return $this->orderRepository->update($orderId, ['status' => $status]);
    }

    public function cancelOrder(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId) {
            $order = $this->orderRepository->find($orderId);
            if ($order && $order->status !== 'cancelled') {
                foreach ($order->orderItems as $item) {
                    $this->inventoryRepository->restoreQuantity($item->product_variant_id, $item->quantity);
                }
                return $this->orderRepository->update($orderId, ['status' => 'cancelled']);
            }
            return false;
        });
    }

    public function getOrdersByUser(int $userId)
    {
        return $this->orderRepository->findByUser($userId);
    }

    public function getOrdersByVendor(int $vendorId)
    {
        return $this->orderRepository->findByVendor($vendorId);
    }

    public function find(int $id)
    {
        return $this->orderRepository->find($id);
    }

    public function getOrdersByStatus(string $status)
    {
        return $this->orderRepository->findByStatus($status);
    }

    private function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $variant = \App\Models\ProductVariant::find($item['product_variant_id']);
            $price = $variant->product->base_price + $variant->price_modifier;
            $total += $price * $item['quantity'];
        }
        return $total;
    }
}