// ============================================================
// revenue-card.component.ts
// Displays total revenue + this month + growth %
// Receives data as @Input() from dashboard.component
// ============================================================
import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule }                              from '@angular/common';
import { DashboardRevenue }                          from '../../../../core/models';

@Component({
  selector:        'app-revenue-card',
  standalone:      true,
  imports:         [CommonModule],
  changeDetection: ChangeDetectionStrategy.OnPush,
  template: `
    <div class="bg-white rounded-xl shadow-md p-5 flex items-start justify-between">

      <div class="space-y-1">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Total Revenue
        </p>
        <p class="text-2xl font-bold text-gray-900 tracking-tight">
          {{ format(revenue.total) }}
        </p>
        <div class="flex items-center gap-2 pt-1">
          <span class="text-xs text-gray-500">This month:</span>
          <span class="text-xs font-semibold text-gray-700">{{ format(revenue.this_month) }}</span>
          <span
            class="text-xs font-bold px-1.5 py-0.5 rounded-full"
            [class]="revenue.growth >= 0
              ? 'text-emerald-700 bg-emerald-100'
              : 'text-red-700 bg-red-100'"
          >
            {{ revenue.growth >= 0 ? '+' : '' }}{{ revenue.growth }}%
          </span>
        </div>
      </div>

      <!-- Icon -->
      <div class="w-11 h-11 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

    </div>
  `,
})
export class RevenueCardComponent {
  @Input({ required: true }) revenue!: DashboardRevenue;

  format(value: number): string {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency', currency: 'EUR',
    }).format(value);
  }
}