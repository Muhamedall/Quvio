// ============================================================
// pending-quotes-card.component.ts
// Shows quote counts by status
// ============================================================
import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule }                              from '@angular/common';
import { RouterLink }                                from '@angular/router';
import { DashboardQuoteStats }                       from '../../../../core/models';

@Component({
  selector:        'app-pending-quotes-card',
  standalone:      true,
  imports:         [CommonModule, RouterLink],
  changeDetection: ChangeDetectionStrategy.OnPush,
  template: `
    <div class="bg-white rounded-xl shadow-md p-5 flex items-start justify-between">

      <div class="space-y-1">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Quotes
        </p>
        <p class="text-2xl font-bold text-gray-900 tracking-tight">
          {{ quotes.total }}
        </p>
        <div class="flex items-center gap-2 pt-1 flex-wrap">
          <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
            {{ quotes.draft }} draft
          </span>
          <span class="text-xs text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full font-semibold">
            {{ quotes.sent }} sent
          </span>
          <span class="text-xs text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full font-semibold">
            {{ quotes.approved }} approved
          </span>
        </div>
      </div>

      <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"
            stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

    </div>
  `,
})
export class PendingQuotesCardComponent {
  @Input({ required: true }) quotes!: DashboardQuoteStats;
}