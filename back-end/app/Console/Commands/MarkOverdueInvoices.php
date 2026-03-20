<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Signature('app:mark-overdue-invoices')]
#[Description('Mark unpaid invoices as overdue when past due date')]
class MarkOverdueInvoices extends Command
{
    /**
     * Execute the console command.
     */
      public function handle(): int
    {
        // Find all invoices past their due date that are still 'unpaid'
        // scopeDuePastDate() is defined on the Invoice model
        $overdueInvoices = Invoice::duePastDate()
            ->with('client')  // load client for the n8n webhook payload
            ->get();
 
        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return self::SUCCESS;
        }
 
        $count = 0;
 
        foreach ($overdueInvoices as $invoice) {
            // Update status to overdue
            $invoice->update(['status' => 'overdue']);
 
            // Fire n8n webhook to send overdue reminder email
            $webhookUrl = config('services.n8n.invoice_overdue_url');
 
            if ($webhookUrl) {
                Http::withoutThrowing()->post($webhookUrl, [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_name'    => $invoice->client->name,
                    'client_email'   => $invoice->client->email,
                    'total'          => $invoice->total,
                    'due_date'       => $invoice->due_date->toDateString(),
                    'days_overdue'   => abs($invoice->days_until_due),
                ]);
            }
 
            $count++;
        }
 
        // Log output for monitoring
        Log::info("Marked {$count} invoices as overdue.");
        $this->info("Marked {$count} invoices as overdue.");
 
        return self::SUCCESS;
    }
}
