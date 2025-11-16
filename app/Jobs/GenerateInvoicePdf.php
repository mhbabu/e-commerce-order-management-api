<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate PDF invoice
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice', ['order' => $this->order]);
        $path = 'invoices/' . $this->order->order_number . '.pdf';
        Storage::put($path, $pdf->output());

        // Save to invoice table
        \App\Models\Invoice::create([
            'order_id' => $this->order->id,
            'pdf_path' => $path,
        ]);
    }
}
