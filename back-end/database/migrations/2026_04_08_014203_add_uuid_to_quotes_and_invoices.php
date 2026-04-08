<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Quote;
use App\Models\Invoice;

// ============================================================
// Adds uuid column to quotes and invoices tables.
// This is used in URLs instead of the integer ID:
//   /quotes/abc123def456  (hard to guess)
//   instead of /quotes/16  (easy to enumerate)
//
// Run: php artisan migrate
// Then the routes use uuid instead of id for security.
// ============================================================

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        // Fill existing records with UUIDs
        Quote::whereNull('uuid')->each(fn($q) => $q->updateQuietly(['uuid' => Str::uuid()]));
        Invoice::whereNull('uuid')->each(fn($i) => $i->updateQuietly(['uuid' => Str::uuid()]));
    }

    public function down(): void
    {
        Schema::table('quotes', fn(Blueprint $t) => $t->dropColumn('uuid'));
        Schema::table('invoices', fn(Blueprint $t) => $t->dropColumn('uuid'));
    }
};