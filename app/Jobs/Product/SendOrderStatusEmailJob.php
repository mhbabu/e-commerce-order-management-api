<?php

namespace App\Jobs\Product;

use App\Events\Product\OrderStatusUpdated;
use App\Mail\OrderStatusUpdateMail;
use App\Models\Order;
use App\Models\User;
use App\Services\Notification\NotificationMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $status
    ) {}

    public function handle(): void
    {
        // send mail to customer
        if ($this->order->user?->email) {
            Mail::to($this->order->user->email)
                ->queue(new OrderStatusUpdateMail($this->order, $this->status));
        }

        // send mail to admins
        $admins = User::where('role', 'admin')->pluck('email')->filter();
        foreach ($admins as $email) {
            Mail::to($email)->queue(new OrderStatusUpdateMail($this->order, $this->status));
        }

        // send mail to vendors
        $vendors = $this->order->orderItems
            ->map(fn($item) => $item->productVariant->product->vendor?->email)
            ->filter()
            ->unique();

        foreach ($vendors as $email) {
            Mail::to($email)->queue(new OrderStatusUpdateMail($this->order, $this->status));
        }

        // broadcast to target customers 
        if (auth('api')->user()->id != $this->order->user_id) { // Skip sending the broadcast if the current user is the customer who created the order; 
            $messageService = new NotificationMessageService(); // otherwise, send the notification to the target customer
            $broadcastTitle = $messageService->generate($this->order, $this->status, 'customer')['title'];
            broadcast(new OrderStatusUpdated($this->order, $broadcastTitle));
        }
    }
}
