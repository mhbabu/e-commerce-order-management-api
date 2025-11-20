<?php

namespace App\Mail;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable
{
    public function __construct(public $variant) {}

    public function build()
    {
        return $this->subject('Low Stock Alert')
            ->markdown('emails.lowstock')
            ->with([
                'product'  => $this->variant->product,
                'variant'  => $this->variant,
                'inventory' => $this->variant->inventory,
            ]);
    }
}
