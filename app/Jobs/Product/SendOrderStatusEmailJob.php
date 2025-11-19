<?php

namespace App\Jobs\Product;

use App\Mail\OrderStatusUpdateMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;
    public string $status;

    public function __construct(Order $order, string $status)
    {
        $this->order  = $order;
        $this->status = $status;
    }

    public function handle(): void
    {
        // Send to customer
        if ($this->order->user && $this->order->user->email) {
            Mail::to($this->order->user->email)
                ->queue(new OrderStatusUpdateMail($this->order, $this->status));
        }

        // Send to admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                Mail::to($admin->email)
                    ->queue(new OrderStatusUpdateMail($this->order, $this->status));
            }
        }

        // Send to vendors
        $vendors = $this->order->orderItems->map(function ($item) {
            return $item->productVariant->product->vendor ?? null;
        })->filter()->unique('id');

        foreach ($vendors as $vendor) {
            if ($vendor->email) {
                Mail::to($vendor->email)
                    ->queue(new OrderStatusUpdateMail($this->order, $this->status));
            }
        }
    }
}
