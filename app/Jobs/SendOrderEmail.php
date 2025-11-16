<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOrderEmail implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $status;

    /**
     * Create a new job instance.
     */
    public function __construct($order, $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send email to user about order status update
        // Mail::to($this->order->user->email)->send(new OrderStatusUpdateMail($this->order, $this->status));
    }
}
