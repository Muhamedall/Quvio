<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Quote;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FillMissingUuids extends Command
{
    protected $signature   = 'quvio:fill-uuids';
    protected $description = 'Fill missing UUIDs on quotes and invoices (run once after migration)';

    public function handle(): void
    {
        $quotes = Quote::whereNull('uuid')->get();
        foreach ($quotes as $q) {
            $q->updateQuietly(['uuid' => (string) Str::uuid()]);
        }
        $this->info("✅ Filled {$quotes->count()} quote UUIDs");

        $invoices = Invoice::whereNull('uuid')->get();
        foreach ($invoices as $i) {
            $i->updateQuietly(['uuid' => (string) Str::uuid()]);
        }
        $this->info("✅ Filled {$invoices->count()} invoice UUIDs");
    }
}