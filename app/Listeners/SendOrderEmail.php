<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\Product\SendOrderStatusEmailJob;
use Illuminate\Support\Facades\Mail;

class SendOrderEmail
{
    public function handle(OrderStatusChanged $event): void
    {
        SendOrderStatusEmailJob::dispatch($event->order, $event->newStatus);
    }
}