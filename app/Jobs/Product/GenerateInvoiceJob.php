<?php

namespace App\Jobs\Product;

use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        // Load relationships
        $this->order->load('user', 'orderItems.productVariant.product');

        // Check if invoice already exists
        if ($this->order->invoice) {
            return;
        }

        // Generate PDF
        $pdf = Pdf::loadView('invoices.order_invoice', ['order' => $this->order]);

        // Filename
        $filename = 'invoice_' . $this->order->order_number . '.pdf';

        // Store PDF directly using Storage
        $path = 'invoices/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());

        // Optional: absolute path
        $localPath = Storage::disk('public')->path($path);

        // Optional: public URL
        $url = Storage::disk('public')->url($path);

        // Create invoice record
        Invoice::create([
            'order_id'     => $this->order->id,
            'pdf_path'     => $url,       // use public URL for access
            'generated_at' => now(),
        ]);
    }
}
