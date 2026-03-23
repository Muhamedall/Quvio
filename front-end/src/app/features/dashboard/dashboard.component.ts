import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }                       from '@angular/common';
import { RouterLink }                         from '@angular/router';

import { DashboardService }            from '../../core/services';
import { DashboardData }               from '../../core/models';
import { LoaderComponent, ButtonComponent,
         BadgeComponent, CardComponent, EmptyStateComponent }
  from '../../shared/components';
import { RevenueCardComponent }        from './widgets/revenue-card/revenue-card';
import { UnpaidInvoicesCardComponent } from './widgets/unpaid-invoices-card/unpaid-invoices-card';
import { PendingQuotesCardComponent }  from './widgets/pending-quotes-card/pending-quotes-card';
import { RecentActivityComponent }     from './widgets/recent-activity/recent-activity';

@Component({
  selector:    'app-dashboard',
  standalone:  true,
  imports: [
    CommonModule,
    RouterLink,
    LoaderComponent,
    ButtonComponent,
    BadgeComponent,
    CardComponent,
    EmptyStateComponent,
    RevenueCardComponent,
    UnpaidInvoicesCardComponent,
    PendingQuotesCardComponent,
    RecentActivityComponent,
  ],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent implements OnInit {

  private dashboardService = inject(DashboardService);

  stats   = signal<DashboardData | null>(null);
  loading = signal(true);
  error   = signal('');

  ngOnInit(): void { this.load(); }

  load(): void {
    this.loading.set(true);
    this.dashboardService.getStats().subscribe({
      next:  (data) => { this.stats.set(data);  this.loading.set(false); },
      error: (err)  => { this.error.set(err.userMessage ?? 'Failed to load.'); this.loading.set(false); },
    });
  }

  // ── GETTERS ───────────────────────────────────────────
  // hasData — used in template: @else if (!hasData)
  // Returns true if the user has created at least one record
  get hasData(): boolean {
    const s = this.stats();
    if (!s) return false;
    return s.invoices.total > 0 || s.quotes.total > 0 || s.clients_count > 0;
  }

  // ── HELPERS ───────────────────────────────────────────
  getBarHeight(revenue: number): number {
    const data = this.stats()?.monthly_revenue ?? [];
    const max  = Math.max(...data.map(m => m.revenue), 1);
    return Math.round((revenue / max) * 100);
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  }

  formatGrowth(growth: number): string {
    const sign = growth >= 0 ? '+' : '';
    return `${sign}${growth}%`;
  }

  isPositiveGrowth(growth: number): boolean {
    return growth >= 0;
  }

  format(value: number): string {
    return this.formatCurrency(value);
  }
}