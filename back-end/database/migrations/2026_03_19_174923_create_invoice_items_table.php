<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// create_invoice_items_table
//
// WHAT IS AN ITEM?
//   One line in a quote or invoice. Examples:
//     - "Web Design"        × 1  @ €1500  = €1500
//     - "SEO Consulting"    × 3  @ €200   = €600
//     - "Logo Design"       × 1  @ €500   = €500
//
// WHY SHARED TABLE for quotes AND invoices?
//   Items belong to EITHER a quote OR an invoice.
//   One of the two foreign keys is always null.
//
//   quote_id = 5, invoice_id = null  → item belongs to quote #5
//   quote_id = null, invoice_id = 3  → item belongs to invoice #3
//
//   This avoids duplicating the table structure.
//   When a quote converts to invoice, items are COPIED
//   (new rows with invoice_id set, quote_id null).
//
// WHY store subtotal?
//   subtotal = quantity × unit_price
//   We store it to avoid recalculating on every query.
//   Always kept in sync by the controller when saving.
// ============================================================

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {

            $table->id();

            // ── Foreign keys (one will always be null) ────
            $table->foreignId('invoice_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete(); // delete items if invoice deleted

            $table->foreignId('quote_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete(); // delete items if quote deleted

            // ── Item details ──────────────────────────────
            $table->string('description');              // service name / description
            $table->decimal('quantity', 8, 2)->default(1);    // e.g. 1, 2.5, 10
            $table->decimal('unit_price', 10, 2);      // price per unit
            $table->decimal('subtotal', 10, 2);        // quantity × unit_price

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};