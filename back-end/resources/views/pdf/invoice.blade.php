<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Invoice {{ $invoice->invoice_number }}</title>
  <style>
    /*
    ============================================================
    DOMPDF STYLE NOTES:
      - No flexbox or grid — DomPDF uses CSS 2.1 only
      - Use float + width for column layouts
      - Use border-collapse for tables
      - All colors must be hex — no rgba
      - No external fonts — use built-in: sans-serif, serif
      - Page size set in controller: A4 portrait
    ============================================================
    */

    * {
      margin:      0;
      padding:     0;
      box-sizing:  border-box;
    }

    body {
      font-family: sans-serif;
      font-size:   13px;
      color:       #374151;
      background:  #ffffff;
      line-height: 1.5;
    }

    /* ── Header band ── */
    .header {
      background-color: #4f46e5;
      color:            #ffffff;
      padding:          32px 40px;
    }

    .header-inner {
      width: 100%;
    }

    .header-left {
      float: left;
      width: 60%;
    }

    .header-right {
      float: right;
      width: 40%;
      text-align: right;
    }

    .header-clear { clear: both; }

    .invoice-title {
      font-size:   28px;
      font-weight: bold;
      color:       #ffffff;
      letter-spacing: 2px;
    }

    .invoice-number {
      font-size: 13px;
      color:     #c7d2fe;
      margin-top: 4px;
    }

    .company-name {
      font-size:   18px;
      font-weight: bold;
      color:       #ffffff;
    }

    .company-sub {
      font-size: 11px;
      color:     #c7d2fe;
      margin-top: 2px;
    }

    /* Status band */
    .status-paid {
      background-color: #10b981;
      color:            #ffffff;
      text-align:       center;
      padding:          6px;
      font-size:        11px;
      font-weight:      bold;
      letter-spacing:   2px;
      text-transform:   uppercase;
    }

    .status-overdue {
      background-color: #ef4444;
      color:            #ffffff;
      text-align:       center;
      padding:          6px;
      font-size:        11px;
      font-weight:      bold;
      letter-spacing:   2px;
      text-transform:   uppercase;
    }

    /* ── Body ── */
    .body {
      padding: 32px 40px;
    }

    /* ── Meta section ── */
    .meta {
      width:         100%;
      margin-bottom: 28px;
    }

    .meta-left {
      float: left;
      width: 50%;
    }

    .meta-right {
      float: right;
      width: 45%;
    }

    .meta-clear { clear: both; }

    .section-label {
      font-size:      9px;
      font-weight:    bold;
      color:          #9ca3af;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom:  6px;
    }

    .client-name {
      font-size:   14px;
      font-weight: bold;
      color:       #111827;
    }

    .client-sub {
      font-size: 12px;
      color:     #6b7280;
      margin-top: 1px;
    }

    /* Meta table (invoice number, dates) */
    .meta-table {
      width:           100%;
      border-collapse: collapse;
    }

    .meta-table td {
      padding:   3px 0;
      font-size: 12px;
    }

    .meta-table .label {
      color: #9ca3af;
      width: 45%;
    }

    .meta-table .value {
      color:       #111827;
      font-weight: 500;
      text-align:  right;
    }

    .meta-table .value-accent {
      color:       #4f46e5;
      font-weight: bold;
      text-align:  right;
    }

    .meta-table .value-paid {
      color:       #10b981;
      font-weight: bold;
      text-align:  right;
    }

    .meta-table .value-overdue {
      color:       #ef4444;
      font-weight: bold;
      text-align:  right;
    }

    /* ── Divider ── */
    .divider {
      border:        none;
      border-top:    1px solid #e5e7eb;
      margin-bottom: 24px;
    }

    /* ── Items table ── */
    .items-table {
      width:           100%;
      border-collapse: collapse;
      margin-bottom:   24px;
    }

    .items-table thead tr {
      background-color: #f9fafb;
    }

    .items-table th {
      padding:        10px 12px;
      font-size:      9px;
      font-weight:    bold;
      color:          #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom:  2px solid #e5e7eb;
    }

    .items-table th.left   { text-align: left; }
    .items-table th.center { text-align: center; }
    .items-table th.right  { text-align: right; }

    .items-table tbody tr {
      border-bottom: 1px solid #f3f4f6;
    }

    .items-table tbody tr:last-child {
      border-bottom: none;
    }

    .items-table td {
      padding:   11px 12px;
      font-size: 12px;
      color:     #374151;
    }

    .items-table td.center { text-align: center; }
    .items-table td.right  { text-align: right; }
    .items-table td.bold   { font-weight: bold; color: #111827; }

    /* ── Totals ── */
    .totals-wrapper {
      width:    100%;
      overflow: hidden;
    }

    .totals-table {
      float:           right;
      width:           240px;
      border-collapse: collapse;
    }

    .totals-table td {
      padding:   5px 0;
      font-size: 13px;
    }

    .totals-table .t-label {
      color: #6b7280;
    }

    .totals-table .t-value {
      text-align:  right;
      font-weight: 500;
      color:       #111827;
    }

    .totals-table .t-total-label {
      font-size:   15px;
      font-weight: bold;
      color:       #111827;
      border-top:  2px solid #111827;
      padding-top: 8px;
    }

    .totals-table .t-total-value {
      font-size:   15px;
      font-weight: bold;
      color:       #4f46e5;
      text-align:  right;
      border-top:  2px solid #111827;
      padding-top: 8px;
    }

    .totals-table .t-total-value-paid {
      font-size:   15px;
      font-weight: bold;
      color:       #10b981;
      text-align:  right;
      border-top:  2px solid #111827;
      padding-top: 8px;
    }

    /* ── Payment link ── */
    .pay-link {
      margin-top:       24px;
      background-color: #eef2ff;
      border:           1px solid #c7d2fe;
      border-radius:    8px;
      padding:          14px 18px;
    }

    .pay-link-label {
      font-size:   10px;
      font-weight: bold;
      color:       #4338ca;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .pay-link-url {
      font-size: 11px;
      color:     #4f46e5;
      word-break: break-all;
    }

    /* ── Notes ── */
    .notes {
      margin-top:   24px;
      border-top:   1px solid #e5e7eb;
      padding-top:  16px;
    }

    .notes-label {
      font-size:      9px;
      font-weight:    bold;
      color:          #9ca3af;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom:  4px;
    }

    .notes-text {
      font-size: 12px;
      color:     #6b7280;
    }

    /* ── Footer ── */
    .footer {
      position:   fixed;
      bottom:     0;
      left:       0;
      right:      0;
      padding:    12px 40px;
      border-top: 1px solid #e5e7eb;
      font-size:  10px;
      color:      #9ca3af;
      text-align: center;
    }

    .footer-left  { float: left; }
    .footer-right { float: right; }
    .footer-clear { clear: both; }

    /* Page break */
    .page-break { page-break-after: always; }

  </style>
</head>
<body>

  {{-- ── FIXED FOOTER (appears on all pages) ─────────────── --}}
  <div class="footer">
    <div class="footer-left">
      {{ $invoice->invoice_number }} · Generated by Quvio
    </div>
    <div class="footer-right">
      {{ now()->format('d/m/Y') }}
    </div>
    <div class="footer-clear"></div>
  </div>

  {{-- ── HEADER BAND ──────────────────────────────────────── --}}
  <div class="header">
    <div class="header-inner">

      {{-- Left: Invoice title --}}
      <div class="header-left">
        <div class="invoice-title">INVOICE</div>
        <div class="invoice-number">{{ $invoice->invoice_number }}</div>
      </div>

      {{-- Right: Company info --}}
      <div class="header-right">
        @php $branding = $user->branding ?? []; @endphp

        <div class="company-name">
          {{ $branding['company_name'] ?? $user->name }}
        </div>
        @if(!empty($branding['company_email']))
          <div class="company-sub">{{ $branding['company_email'] }}</div>
        @endif
        @if(!empty($branding['company_phone']))
          <div class="company-sub">{{ $branding['company_phone'] }}</div>
        @endif
        @if(!empty($branding['company_website']))
          <div class="company-sub">{{ $branding['company_website'] }}</div>
        @endif
      </div>

      <div class="header-clear"></div>
    </div>
  </div>

  {{-- ── STATUS BAND ──────────────────────────────────────── --}}
  @if($invoice->status === 'paid')
    <div class="status-paid">✓ PAID</div>
  @elseif($invoice->status === 'overdue')
    <div class="status-overdue">OVERDUE</div>
  @endif

  {{-- ── BODY ────────────────────────────────────────────── --}}
  <div class="body">

    {{-- ── META: billed to + invoice info ──────────────── --}}
    <div class="meta">

      {{-- Billed to --}}
      <div class="meta-left">
        <div class="section-label">Billed To</div>
        <div class="client-name">
          {{ $invoice->client->company ?? $invoice->client->name }}
        </div>
        @if($invoice->client->company)
          <div class="client-sub">{{ $invoice->client->name }}</div>
        @endif
        <div class="client-sub">{{ $invoice->client->email }}</div>
        @if($invoice->client->phone)
          <div class="client-sub">{{ $invoice->client->phone }}</div>
        @endif
        @if($invoice->client->address)
          <div class="client-sub" style="margin-top: 4px;">
            {{ $invoice->client->address }}
          </div>
        @endif
      </div>

      {{-- Invoice details --}}
      <div class="meta-right">
        <table class="meta-table">
          <tr>
            <td class="label">Invoice Number</td>
            <td class="value-accent">{{ $invoice->invoice_number }}</td>
          </tr>
          <tr>
            <td class="label">Date Issued</td>
            <td class="value">{{ $invoice->created_at->format('d/m/Y') }}</td>
          </tr>
          <tr>
            <td class="label">Due Date</td>
            <td class="
              @if($invoice->status === 'overdue') value-overdue
              @else value
              @endif
            ">
              {{ $invoice->due_date->format('d/m/Y') }}
            </td>
          </tr>
          @if($invoice->paid_at)
            <tr>
              <td class="label">Paid On</td>
              <td class="value-paid">{{ $invoice->paid_at->format('d/m/Y') }}</td>
            </tr>
          @endif
          @if(!empty($branding['company_address']))
            <tr>
              <td class="label">From</td>
              <td class="value" style="font-size: 10px;">
                {{ $branding['company_address'] }}
              </td>
            </tr>
          @endif
        </table>
      </div>

      <div class="meta-clear"></div>
    </div>

    <hr class="divider" />

    {{-- ── LINE ITEMS TABLE ──────────────────────────────── --}}
    <table class="items-table">
      <thead>
        <tr>
          <th class="left" style="width: 50%">Description</th>
          <th class="center" style="width: 12%">Qty</th>
          <th class="right" style="width: 18%">Unit Price</th>
          <th class="right" style="width: 20%">Amount</th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoice->items as $item)
          <tr>
            <td>{{ $item->description }}</td>
            <td class="center">{{ number_format($item->quantity, 0) }}</td>
            <td class="right">
              {{ number_format($item->unit_price, 2, ',', ' ') }} €
            </td>
            <td class="right bold">
              {{ number_format($item->subtotal, 2, ',', ' ') }} €
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- ── TOTALS ─────────────────────────────────────────── --}}
    <div class="totals-wrapper">
      <table class="totals-table">
        <tr>
          <td class="t-label">Subtotal</td>
          <td class="t-value">{{ number_format($invoice->subtotal, 2, ',', ' ') }} €</td>
        </tr>
        <tr>
          <td class="t-label">Tax ({{ $invoice->tax_rate }}%)</td>
          <td class="t-value">
            {{ number_format($invoice->total - $invoice->subtotal, 2, ',', ' ') }} €
          </td>
        </tr>
        <tr>
          <td class="t-total-label">TOTAL</td>
          <td class="{{ $invoice->status === 'paid' ? 't-total-value-paid' : 't-total-value' }}">
            {{ number_format($invoice->total, 2, ',', ' ') }} €
          </td>
        </tr>
      </table>
    </div>

    {{-- ── STRIPE PAYMENT LINK ──────────────────────────── --}}
    @if($invoice->stripe_link && $invoice->status !== 'paid')
      <div class="pay-link">
        <div class="pay-link-label">Pay Online</div>
        <div class="pay-link-url">{{ $invoice->stripe_link }}</div>
      </div>
    @endif

    {{-- ── NOTES ──────────────────────────────────────────── --}}
    @php
      $notes = $invoice->notes
        ?? ($branding['invoice_notes'] ?? null);
    @endphp

    @if($notes)
      <div class="notes">
        <div class="notes-label">Notes</div>
        <div class="notes-text">{{ $notes }}</div>
      </div>
    @endif

  </div>{{-- end .body --}}

</body>
</html>