<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\Product\GenerateInvoiceJob;

class GenerateInvoice
{
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->newStatus === 'delivered') {
            GenerateInvoiceJob::dispatch($event->order);
        }
    }
}