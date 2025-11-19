<?php

namespace App\Jobs\Product;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusUpdateMail;

class SendOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;
    public string $newStatus;
    public string $recipientEmail;

    public function __construct(Order $order, string $newStatus, string $recipientEmail)
    {
        $this->order = $order;
        $this->newStatus = $newStatus;
        $this->recipientEmail = $recipientEmail;
    }

    public function handle(): void
    {
        Mail::to($this->recipientEmail)
            ->send(new OrderStatusUpdateMail($this->order, $this->newStatus));
    }
}
