<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\InvoiceController;

class SyncInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sistemdeki faturaları arka planda senkronize eder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $this->info('Senkronizasyon başladı...');
            InvoiceController::SyncInvoices();
        } catch (\Throwable $e) {
            \Log::error('SyncInvoicesCommand hatası: ' . $e->getMessage());
            $this->error('Hata: ' . $e->getMessage());
        }
    }

}
