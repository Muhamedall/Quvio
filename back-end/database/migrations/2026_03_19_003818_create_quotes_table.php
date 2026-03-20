<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            // ── Foreign keys ──────────────────────────────
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
 
            $table->foreignId('client_id')
                  ->constrained()
                  ->cascadeOnDelete();


          // ── Quote identity ────────────────────────────

        // unique() = no two quotes can have the same number
        $table->string('quote_number')->unique();


        // ── Status ────────────────────────────────────

            // enum() = only these exact values are allowed
            // database enforces this — no invalid statuses possible
            $table->enum('status', [
                'draft',      // created but not sent yet
                'sent',       // emailed to client
                'approved',   // client said yes
                'rejected',   // client said no
            ])->default('draft'); // new quotes start as draft

        // ── Amounts ───────────────────────────────────

            // decimal(10, 2) = up to 10 digits, 2 decimal places
            // e.g. 99999999.99 — enough for any invoice amount
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);  // percentage e.g. 20.00
            $table->decimal('total', 10, 2)->default(0);
        
         // ── Optional fields ───────────────────────────
          $table->text('notes')->nullable();         // internal or client-facing notes
          $table->date('valid_until')->nullable();   // quote expiry date


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
