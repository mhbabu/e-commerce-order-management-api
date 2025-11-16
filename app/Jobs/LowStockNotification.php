<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LowStockNotification implements ShouldQueue
{
    use Queueable;

    protected $variant;
    protected $stock;

    /**
     * Create a new job instance.
     */
    public function __construct($variant, $stock)
    {
        $this->variant = $variant;
        $this->stock = $stock;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send low stock notification to vendor
        // Mail::to($this->variant->product->vendor->email)->send(new LowStockAlertMail($this->variant, $this->stock));
    }
}
