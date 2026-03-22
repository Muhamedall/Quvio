import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }                       from '@angular/common';
import { RouterLink }                         from '@angular/router';

import { DashboardService }              from '../../../core/services';
import { DashboardData }                 from '../../../core/models';
import { LoaderComponent  , ButtonComponent }               from '../../../shared/components';
import { RevenueCardComponent }          from './widgets/revenue-card/revenue-card.component';
import { UnpaidInvoicesCardComponent }   from './widgets/unpaid-invoices-card/unpaid-invoices-card.component';
import { PendingQuotesCardComponent }    from './widgets/pending-quotes-card/pending-quotes-card.component';
import { RecentActivityComponent }       from './widgets/recent-activity/recent-activity.component';

@Component({
  selector:    'app-dashboard',
  standalone:  true,
  imports: [
    CommonModule,
    RouterLink,
    LoaderComponent,
    ButtonComponent,
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

  getBarHeight(revenue: number): number {
    const data = this.stats()?.monthly_revenue ?? [];
    const max  = Math.max(...data.map(m => m.revenue), 1);
    return Math.round((revenue / max) * 100);
  }

  format(value: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  }
}