import { Component, inject, OnInit, signal, computed } from '@angular/core';
import { CommonModule }    from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { QuoteService }    from '../../../core/services';
import { Quote, QuoteStatus } from '../../../core/models';
import {
  ButtonComponent, BadgeComponent, TableComponent,
  LoaderComponent, EmptyStateComponent,
} from '../../../shared/components';

@Component({
  selector:    'app-quote-list',
  standalone:  true,
  imports:     [CommonModule, RouterLink, ButtonComponent, BadgeComponent,
                TableComponent, LoaderComponent, EmptyStateComponent],
  templateUrl: './quote-list.component.html',
})
export class QuoteListComponent implements OnInit {

  private quoteService = inject(QuoteService);
  private router       = inject(Router);

  quotes      = signal<Quote[]>([]);
  loading     = signal(true);
  filterStatus = signal<QuoteStatus | 'all'>('all');

  readonly columns = [
    { label: 'Number',  key: 'quote_number' },
    { label: 'Client' },
    { label: 'Total',   key: 'total', align: 'right' as const },
    { label: 'Status',  key: 'status' },
    { label: 'Date' },
    { label: 'Actions' },
  ];

  readonly statuses: { value: QuoteStatus | 'all'; label: string }[] = [
    { value: 'all',      label: 'All' },
    { value: 'draft',    label: 'Draft' },
    { value: 'sent',     label: 'Sent' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
  ];

  // Filtered quotes based on selected status
  filtered = computed(() => {
    const status = this.filterStatus();
    if (status === 'all') return this.quotes();
    return this.quotes().filter(q => q.status === status);
  });

  ngOnInit(): void { this.load(); }

  load(): void {
    this.loading.set(true);
    this.quoteService.getAll().subscribe({
      next:  (data) => { this.quotes.set(data); this.loading.set(false); },
      error: ()     => { this.loading.set(false); },
    });
  }

  setFilter(status: QuoteStatus | 'all'): void {
    this.filterStatus.set(status);
  }

  goToNew(): void {
    this.router.navigate(['/quotes/new']);
  }

  goToDetail(id: number): void {
    this.router.navigate(['/quotes', id]);
  }

  goToEdit(id: number, event: Event): void {
    event.stopPropagation();
    this.router.navigate(['/quotes', id, 'edit']);
  }

  delete(quote: Quote, event: Event): void {
    event.stopPropagation();
    if (!confirm(`Delete ${quote.quote_number}?`)) return;
    this.quoteService.destroy(quote.id).subscribe(() => this.load());
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  }
}