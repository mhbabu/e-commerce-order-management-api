<?php

namespace App\Services\Product;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Repositories\Product\InventoryRepository;
use App\Repositories\Product\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected OrderRepository $orderRepository;
    protected InventoryRepository $inventoryRepository;

    public function __construct(OrderRepository $orderRepository, InventoryRepository $inventoryRepository)
    {
        $this->orderRepository     = $orderRepository;
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * Get paginated orders based on filters
     */
    /**
     * Get paginated orders based on filters
     */
    public function getOrders(array $filteringData, int $userId, string $role)
    {
        // Base query
        if ($role === 'customer') {
            $query = $this->orderRepository->getByUserQuery($userId);
        } elseif ($role === 'vendor') {
            $query = $this->orderRepository->getByVendorQuery($userId);
        } else { // admin
            $query = $this->orderRepository->getModel()->newQuery();
        }

        // Filter by status
        if (!empty($filteringData['status'])) {
            $query->where('status', $filteringData['status']);
        }

        // Search only by order_number
        if (!empty($filteringData['search'])) {
            $query->where('order_number', 'LIKE', '%' . $filteringData['search'] . '%');
        }

        $query->latest(); // orders by created_at DESC

        // Pagination
        return $query->with('orderItems.productVariant.product', 'user')
            ->paginate($filteringData['per_page'], ['*'], 'page', $filteringData['page']);
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
                'user_id'      => $userId,
                'total_amount' => $total,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status'       => 'pending'
            ]));

            foreach ($items as $item) {
                $variant = ProductVariant::find($item['product_variant_id']);
                $price   = $variant->product->base_price + $variant->price_modifier;
                OrderItem::create(array_merge($item, ['order_id' => $order->id, 'price' => $price]));
                // Deduct inventory
                $this->inventoryRepository->deductQuantity($item['product_variant_id'], $item['quantity']);
            }

            return $order;
        });
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        $order = $this->orderRepository->find($orderId);
        if (!$order) return false;

        $oldStatus = $order->status;

        $updated = $this->orderRepository->update($orderId, ['status' => $status]);

        if ($updated && $oldStatus !== $status) {
            event(new OrderStatusChanged($order, $oldStatus, $status));
        }

        return $updated;
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

    private function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $variant = ProductVariant::find($item['product_variant_id']);
            $price   = $variant->product->base_price + $variant->price_modifier;
            $total += $price * $item['quantity'];
        }
        return $total;
    }

    public function find(int $id)
    {
        return $this->orderRepository->find($id);
    }
}
