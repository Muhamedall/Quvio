<?php

namespace App\Http\Controllers\Api;

// ============================================================
// DashboardController.php  —  Laravel 13
//
// ONE ENDPOINT:
//   GET /api/dashboard
//
// RETURNS everything the Angular dashboard needs in ONE request:
//   - Revenue stats (total, this month, last month)
//   - Invoice counts by status
//   - Quote counts by status
//   - Total clients count
//   - Recent invoices (last 5)
//   - Recent quotes (last 5)
//   - Monthly revenue chart data (last 6 months)
//
// WHY ONE BIG ENDPOINT INSTEAD OF MANY SMALL ONES?
//   The dashboard needs data from 3 different models.
//   If Angular made 6 separate requests, the page would
//   show a loading spinner 6 times and make 6 round trips.
//
//   One request = one loading state = instant dashboard.
//
// PERFORMANCE — we use:
//   selectRaw()  → do math in MySQL, not PHP loops
//   whereMonth() → filter by current month in SQL
//   withCount()  → count relations in one JOIN, not N+1
//   All queries are scoped to auth()->user() automatically
// ============================================================

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // GET /api/dashboard
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'revenue'         => $this->getRevenueStats($user),
            'invoices'        => $this->getInvoiceStats($user),
            'quotes'          => $this->getQuoteStats($user),
            'clients_count'   => $user->clients()->count(),
            'recent_invoices' => $this->getRecentInvoices($user),
            'recent_quotes'   => $this->getRecentQuotes($user),
            'monthly_revenue' => $this->getMonthlyRevenue($user),
        ]);
    }

    // ── REVENUE STATS ────────────────────────────────────
    //
    // Returns three revenue numbers:
    //   total        → all time paid invoices
    //   this_month   → current month paid
    //   last_month   → previous month paid
    //   growth       → % change between this and last month
    //
    // selectRaw('SUM(total) as revenue') = MySQL does the SUM
    // value('revenue') = pluck just that one value (not a Collection)
    private function getRevenueStats(mixed $user): array
    {
        $baseQuery = fn () => $user->invoices()->where('status', 'paid');

        // Total revenue all time
        $total = $baseQuery()->sum('total');

        // This month revenue
        $thisMonth = $baseQuery()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        // Last month revenue
        $lastMonth = $baseQuery()
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('total');

        // Growth percentage vs last month
        // Avoid division by zero — if last month was 0, growth is 100% if we earned anything
        $growth = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100 : 0);

        return [
            'total'       => (float) $total,
            'this_month'  => (float) $thisMonth,
            'last_month'  => (float) $lastMonth,
            'growth'      => $growth,  // e.g. 23.5 means +23.5%
        ];
    }

    // ── INVOICE STATS ────────────────────────────────────
    //
    // Count invoices by each status.
    // Also calculates total amount of unpaid invoices (outstanding).
    //
    // groupBy() + selectRaw() = one SQL query for all counts
    // vs calling where('status', X)->count() 3 times separately
    private function getInvoiceStats(mixed $user): array
    {
        // One query → counts grouped by status
        // Result: [['status' => 'paid', 'count' => 12], ...]
        $counts = $user->invoices()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');  // ['paid' => 12, 'unpaid' => 5, ...]

        return [
            'total'       => $user->invoices()->count(),
            'unpaid'      => (int) ($counts['unpaid']  ?? 0),
            'paid'        => (int) ($counts['paid']    ?? 0),
            'overdue'     => (int) ($counts['overdue'] ?? 0),

            // Total money currently outstanding (unpaid + overdue)
            'outstanding' => (float) $user->invoices()
                ->whereIn('status', ['unpaid', 'overdue'])
                ->sum('total'),
        ];
    }

    // ── QUOTE STATS ──────────────────────────────────────
    private function getQuoteStats(mixed $user): array
    {
        $counts = $user->quotes()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'total'    => $user->quotes()->count(),
            'draft'    => (int) ($counts['draft']    ?? 0),
            'sent'     => (int) ($counts['sent']     ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
        ];
    }

    // ── RECENT INVOICES ──────────────────────────────────
    //
    // Last 5 invoices for the "Recent Activity" card.
    // We load client relationship to show client name.
    // We select only the columns Angular needs — not the full model.
    private function getRecentInvoices(mixed $user): array
    {
        return $user->invoices()
            ->with('client:id,name,company')  // load only needed columns
            ->select([
                'id',
                'invoice_number',
                'client_id',
                'status',
                'total',
                'due_date',
                'paid_at',
                'created_at',
            ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'id'             => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_name'    => $invoice->client->company ?? $invoice->client->name,
                'status'         => $invoice->status,
                'status_label'   => $invoice->status_label,
                'total'          => (float) $invoice->total,
                'due_date'       => $invoice->due_date->toDateString(),
            ])
            ->toArray();
    }

    // ── RECENT QUOTES ────────────────────────────────────
    private function getRecentQuotes(mixed $user): array
    {
        return $user->quotes()
            ->with('client:id,name,company')
            ->select([
                'id',
                'quote_number',
                'client_id',
                'status',
                'total',
                'created_at',
            ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Quote $quote) => [
                'id'           => $quote->id,
                'quote_number' => $quote->quote_number,
                'client_name'  => $quote->client->company ?? $quote->client->name,
                'status'       => $quote->status,
                'status_label' => $quote->status_label,
                'total'        => (float) $quote->total,
                'created_at'   => $quote->created_at->toDateString(),
            ])
            ->toArray();
    }

    // ── MONTHLY REVENUE CHART ────────────────────────────
    //
    // Returns revenue data for the last 6 months.
    // Used to draw the revenue bar/line chart in Angular.
    //
    // EXAMPLE OUTPUT:
    //   [
    //     { "month": "Oct 2025", "revenue": 4200.00 },
    //     { "month": "Nov 2025", "revenue": 6100.00 },
    //     { "month": "Dec 2025", "revenue": 3800.00 },
    //     { "month": "Jan 2026", "revenue": 5250.00 },
    //     { "month": "Feb 2026", "revenue": 7100.00 },
    //     { "month": "Mar 2026", "revenue": 4900.00 },
    //   ]
    //
    // selectRaw with DATE_FORMAT = MySQL formats the date for us
    // This is much faster than loading all invoices and grouping in PHP
    private function getMonthlyRevenue(mixed $user): array
    {
        // Build an array of the last 6 months as ['YYYY-MM' => 'Mon YYYY']
        // We use this to fill in 0 for months with no revenue
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->put(
                $date->format('Y-m'),       // key:   '2025-10'
                $date->format('M Y')        // label: 'Oct 2025'
            );
        }

        // Query: sum revenue grouped by year-month, last 6 months only
        $revenues = $user->invoices()
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month_key, SUM(total) as revenue")
            ->groupBy('month_key')
            ->pluck('revenue', 'month_key');  // ['2025-10' => 4200, ...]

        // Merge: for each of the 6 months, use queried revenue or 0
        return $months->map(function (string $label, string $key) use ($revenues): array {
            return [
                'month'   => $label,
                'revenue' => (float) ($revenues[$key] ?? 0),
            ];
        })->values()->toArray();
    }
}
