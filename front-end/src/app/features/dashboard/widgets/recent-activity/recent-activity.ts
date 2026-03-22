// ============================================================
// recent-activity.component.ts
// Shows the last 5 invoices + last 5 quotes side by side
// ============================================================
import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule }                              from '@angular/common';
import { RouterLink }                                from '@angular/router';
import { RecentInvoice, RecentQuote }                 from '../../../../core/models';
import { BadgeComponent }                            from '../../../../shared/components';

@Component({
  selector:        'app-recent-activity',
  standalone:      true,
  imports:         [CommonModule, RouterLink, BadgeComponent],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl:     './recent-activity.html',
})
export class RecentActivityComponent {
  @Input({ required: true }) recentInvoices!: RecentInvoice[];
  @Input({ required: true }) recentQuotes!:   RecentQuote[];

  format(value: number): string {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency', currency: 'EUR',
    }).format(value);
  }
}