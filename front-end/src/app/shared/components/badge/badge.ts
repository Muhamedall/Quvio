// ============================================================
// badge.component.ts
//
// Handles all status labels: Paid, Unpaid, Draft, Overdue…
// Pass a [status] and it auto-picks the right color.
// Or pass a [variant] directly for manual control.
//
// USAGE:
//   <app-badge status="paid">Paid</app-badge>
//   <app-badge status="overdue">Overdue</app-badge>
//   <app-badge variant="info">Sent</app-badge>
//   <app-badge variant="neutral" [dot]="true">Draft</app-badge>
// ============================================================

import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';

export type BadgeVariant  = 'success' | 'warning' | 'danger' | 'info' | 'primary' | 'neutral';
export type BadgeStatus   = 'paid' | 'unpaid' | 'overdue' | 'draft' | 'sent' | 'approved' | 'rejected' | 'pending';

@Component({
  selector: 'app-badge',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './badge.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BadgeComponent {

  @Input() variant: BadgeVariant = 'neutral';
  @Input() status: BadgeStatus | '' = '';  // Auto-maps to a variant
  @Input() dot = false;                    // Show a colored dot before label
  @Input() size: 'sm' | 'md' = 'sm';

  // Maps semantic status string → variant color
  private readonly statusMap: Record<BadgeStatus, BadgeVariant> = {
    paid:     'success',
    approved: 'success',
    unpaid:   'warning',
    pending:  'warning',
    overdue:  'danger',
    rejected: 'danger',
    sent:     'info',
    draft:    'neutral',
  };

  // Color classes per variant — bg + text combination
  private readonly variantClasses: Record<BadgeVariant, string> = {
    success: 'bg-emerald-100 text-emerald-700',
    warning: 'bg-amber-100   text-amber-700',
    danger:  'bg-red-100     text-red-700',
    info:    'bg-blue-100    text-blue-700',
    primary: 'bg-indigo-100  text-indigo-700',
    neutral: 'bg-gray-100    text-gray-600',
  };

  // Dot color per variant
  readonly dotClasses: Record<BadgeVariant, string> = {
    success: 'bg-emerald-500',
    warning: 'bg-amber-500',
    danger:  'bg-red-500',
    info:    'bg-blue-500',
    primary: 'bg-indigo-500',
    neutral: 'bg-gray-400',
  };

  get resolvedVariant(): BadgeVariant {
    return this.status ? (this.statusMap[this.status] ?? this.variant) : this.variant;
  }

  get badgeClasses(): string {
    const sizeClass = this.size === 'sm'
      ? 'px-2 py-0.5 text-xs'
      : 'px-2.5 py-1 text-sm';

    return [
      'inline-flex items-center gap-1 rounded-full font-semibold uppercase tracking-wide',
      sizeClass,
      this.variantClasses[this.resolvedVariant],
    ].join(' ');
  }

  get resolvedDotClass(): string {
    return this.dotClasses[this.resolvedVariant];
  }
}