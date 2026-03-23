<?php

namespace App\Http\Controllers\Api;

// ============================================================
// PdfController.php  —  Laravel 13
//
// Handles PDF generation for invoices and quotes.
// Uses barryvdh/laravel-dompdf package.
//
// ENDPOINTS:
//   GET /api/invoices/{invoice}/pdf  → download invoice PDF
//   GET /api/quotes/{quote}/pdf      → download quote PDF
//
// HOW DOMPDF WORKS:
//   1. We pass data to a Blade template
//   2. DomPDF renders the HTML → converts to PDF
//   3. We stream it back as a download
//
// IMPORTANT — DomPDF limitations:
//   - No flexbox or CSS grid
//   - No external images from URLs (use base64)
//   - Supports CSS 2.1 only
//   - Use float + width for multi-column layouts
// ============================================================

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    // GET /api/invoices/{invoice}/pdf
    public function invoicePdf(Request $request, int $id): Response
    {
        // Find invoice scoped to the logged-in user
        $invoice = $request->user()
            ->invoices()
            ->with(['client', 'items', 'quote'])
            ->findOrFail($id);

        $user = $request->user();

        // Generate PDF from Blade template
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'user'    => $user,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isRemoteEnabled', false)   // security: no remote URLs
        ->setOption('isPhpEnabled', false)      // security: no PHP in blade
        ->setOption('defaultFont', 'sans-serif')
        ->setOption('dpi', 150);

        // download() = browser downloads the PDF file
        // stream()   = browser opens PDF in a new tab
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    // GET /api/quotes/{quote}/pdf
    public function quotePdf(Request $request, int $id): Response
    {
        $quote = $request->user()
            ->quotes()
            ->with(['client', 'items'])
            ->findOrFail($id);

        $user = $request->user();

        $pdf = Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'user'  => $user,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isRemoteEnabled', false)
        ->setOption('isPhpEnabled', false)
        ->setOption('defaultFont', 'sans-serif')
        ->setOption('dpi', 150);

        return $pdf->download("quote-{$quote->quote_number}.pdf");
    }
}
