<?php 

namespace App\Jobs\Product;

use App\Mail\LowStockAlertMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendLowStockAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public $order) {}

    public function handle()
    {
        foreach ($this->order->orderItems as $item) {
            $variant   = $item->productVariant;
            $inventory = $variant->inventory;

            if ($inventory->quantity <= $inventory->low_stock_threshold) {
                $vendorEmail = $variant->product->vendor?->email;

                if ($vendorEmail) {
                    Mail::to($vendorEmail)->queue(
                        new LowStockAlertMail($variant)
                    );
                }
            }
        }
    }
}
