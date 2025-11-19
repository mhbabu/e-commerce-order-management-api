<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Mail;

class SendOrderEmail
{
    public function handle(OrderStatusChanged $event): void
    {

        info('babu');
        // Send email notification to user about order status change
        // Mail::to($event->order->user->email)->send(new OrderStatusUpdate($event->order, $event->newStatus));
    }
}