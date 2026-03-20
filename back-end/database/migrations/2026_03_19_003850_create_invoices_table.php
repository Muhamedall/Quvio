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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            // ── Foreign keys ──────────────────────────────

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('client_id')
                  ->constrained()
                  ->cascadeOnDelete();
             // nullable() because invoice can exist without a quote
            $table->foreignId('quote_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete(); // if quote deleted → keep invoice, set quote_id to null
            
            
            // ── Invoice identity ──────────────────────────
            $table->string('invoice_number')->unique();

                        // ── Status ────────────────────────────────────
            $table->enum('status', [
                'unpaid',   // default — waiting for payment
                'paid',     // Stripe confirmed payment
                'overdue',  // past due_date, still unpaid
            ])->default('unpaid');


            // ── Amounts ───────────────────────────────────
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // ── Dates ─────────────────────────────────────
            $table->date('due_date');                  // required — payment deadline
            $table->timestamp('paid_at')->nullable();  // set when Stripe webhook fires

            // ── Stripe ────────────────────────────────────
            // Both nullable — only set after Stripe payment link is created
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_link')->nullable();  // URL sent to client

            // ── Optional ──────────────────────────────────
            $table->text('notes')->nullable();
        
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
