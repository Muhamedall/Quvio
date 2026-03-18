// ============================================================
// card.component.ts
//
// The main visual container for all content in Quvio.
// Supports optional title header, loading skeletons,
// hoverable state for clickable stat cards, and slots
// for header actions and footer content.
//
// USAGE:
//   <!-- Simple -->
//   <app-card>Content here</app-card>
//
//   <!-- With header -->
//   <app-card title="Recent Invoices" [loading]="isLoading">
//     <app-button slot="action" variant="ghost" size="sm">View all</app-button>
//     ... table rows ...
//   </app-card>
//
//   <!-- Clickable stat card -->
//   <app-card [hoverable]="true" (cardClick)="goToInvoices()">
//     <span class="text-3xl font-bold">€ 4,250</span>
//   </app-card>
// ============================================================

import {
  Component, Input, Output, EventEmitter, ChangeDetectionStrategy
} from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-card',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './card.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CardComponent {

  @Input() title = '';
  @Input() subtitle = '';
  @Input() loading = false;
  @Input() hoverable = false;
  @Input() outlined = false;             // Border instead of shadow
  @Input() padding: 'none' | 'sm' | 'md' | 'lg' = 'md';

  @Output() cardClick = new EventEmitter<void>();

  private readonly paddingClasses = {
    none: 'p-0',
    sm:   'p-4',
    md:   'p-6',
    lg:   'p-8',
  };

  get cardClasses(): string {
    return [
      'bg-white rounded-xl overflow-hidden transition-all duration-200',
      this.outlined
        ? 'border border-gray-200'
        : 'shadow-md',
      this.hoverable
        ? 'cursor-pointer hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0'
        : '',
    ].filter(Boolean).join(' ');
  }

  get bodyClasses(): string {
    return this.paddingClasses[this.padding];
  }

  onClick(): void {
    if (this.hoverable) this.cardClick.emit();
  }
}