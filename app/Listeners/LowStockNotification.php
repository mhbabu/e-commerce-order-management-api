<?php

namespace App\Listeners;

use App\Events\LowStockAlert;
use Illuminate\Support\Facades\Log;

class LowStockNotification
{
    public function handle(LowStockAlert $event): void
    {
        // Log low stock alert or send notification to vendor
        Log::warning("Low stock alert for product variant {$event->variant->id}: {$event->currentStock} remaining");
        // Could send email to vendor: Mail::to($event->variant->product->vendor->email)->send(new LowStockAlertMail($event->variant));
    }
}