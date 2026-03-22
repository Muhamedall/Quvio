// ============================================================
// unpaid-invoices-card.component.ts
// Shows unpaid + overdue invoice counts and outstanding amount
// ============================================================
import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule }                              from '@angular/common';
import { RouterLink }                                from '@angular/router';
import { DashboardInvoiceStats }                     from '../../../../core/models';

@Component({
  selector:        'app-unpaid-invoices-card',
  standalone:      true,
  imports:         [CommonModule, RouterLink],
  changeDetection: ChangeDetectionStrategy.OnPush,
  template: `
    <div class="bg-white rounded-xl shadow-md p-5 flex items-start justify-between">

      <div class="space-y-1">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Outstanding
        </p>
        <p class="text-2xl font-bold text-gray-900 tracking-tight">
          {{ format(invoices.outstanding) }}
        </p>
        <div class="flex items-center gap-3 pt-1">
          <span class="inline-flex items-center gap-1 text-xs text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full font-semibold">
            {{ invoices.unpaid }} unpaid
          </span>
          @if (invoices.overdue > 0) {
            <span class="inline-flex items-center gap-1 text-xs text-red-700 bg-red-100 px-2 py-0.5 rounded-full font-semibold">
              {{ invoices.overdue }} overdue
            </span>
          }
        </div>
      </div>

      <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

    </div>
  `,
})
export class UnpaidInvoicesCardComponent {
  @Input({ required: true }) invoices!: DashboardInvoiceStats;

  format(value: number): string {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency', currency: 'EUR',
    }).format(value);
  }
}