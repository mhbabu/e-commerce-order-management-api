<?php

namespace App\Services\Notification;

use App\Models\Order;

class NotificationMessageService
{
    /**
     * Generate dynamic notification title for order status.
     */
    public function generate(Order $order, string $status, string $recipientType): array
    {
        $title = $this->generateTitle($order, $status, $recipientType);
        return ['title' => $title];
    }

    protected function generateTitle(Order $order, string $status, string $recipientType): string
    {
        return match ($recipientType) {
            'customer' => match ($status) {
                'processing' => "Your Order #{$order->order_number} is being processed",
                'shipped'    => "Your Order #{$order->order_number} has been shipped",
                'delivered'  => "Your Order #{$order->order_number} has been delivered",
                'cancelled'  => "Your Order #{$order->order_number} has been cancelled",
                default      => "Update on your Order #{$order->order_number}",
            },
            'admin' => match ($status) {
                'processing' => "Order #{$order->order_number} is now processing",
                'shipped'    => "Order #{$order->order_number} has been shipped",
                'delivered'  => "Order #{$order->order_number} delivered successfully",
                'cancelled'  => "Order #{$order->order_number} has been cancelled",
                'low_stock'  => "Low stock alert for order #{$order->order_number}",
                default      => "Order #{$order->order_number} status updated",
            },
            'vendor' => match ($status) {
                'processing' => "Order #{$order->order_number} is being processed for your product",
                'shipped'    => "Order #{$order->order_number} has been shipped",
                'delivered'  => "Order #{$order->order_number} delivered to customer",
                'cancelled'  => "Order #{$order->order_number} cancelled for your product",
                'low_stock'  => "Low stock alert for your products in order #{$order->order_number}",
                default      => "Order #{$order->order_number} status updated",
            },
            default => "Order #{$order->order_number} status updated",
        };
    }

    /**
     * Generate low stock alert for multiple variants
     */
    public function generateLowStockMessage(array $variants, string $recipientType): array
    {
        $titles = [];
        $lines  = [];

        foreach ($variants as $variant) {
            $titles[] = $variant->product->title;
            $lines[]  = "{$variant->product->title} ({$variant->variant_name}) - Stock: {$variant->quantity}, Threshold: {$variant->low_stock_threshold}";
        }

        $title = match ($recipientType) {
            'admin'  => "Low stock alert: " . implode(', ', $titles),
            'vendor' => "Low stock alert for your products: " . implode(', ', $titles),
            default  => "Low stock alert: " . implode(', ', $titles),
        };

        $body = implode("\n", $lines);

        return ['title' => $title, 'body' => $body];
    }
}
