<?php

namespace App\Jobs;

use App\Http\Controllers\InvoiceController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            InvoiceController::getInvoice();
        } catch (\Throwable $e) {
            \Log::error('Fatura senkronizasyon hatası: ' . $e->getMessage());
        }
    }
}
