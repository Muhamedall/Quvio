import { Component, inject, OnInit, signal, computed } from '@angular/core';
import { CommonModule }       from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { QuoteService }       from '../../../core/services';
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

  quotes       = signal<Quote[]>([]);
  loading      = signal(true);
  filterStatus = signal<QuoteStatus | 'all'>('all');

  readonly columns = [
    { label: 'Number' },
    { label: 'Client' },
    { label: 'Total', align: 'right' as const },
    { label: 'Status' },
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

  filtered = computed(() => {
    const s = this.filterStatus();
    return s === 'all' ? this.quotes() : this.quotes().filter(q => q.status === s);
  });

  ngOnInit(): void { this.load(); }

  load(): void {
    this.loading.set(true);
    this.quoteService.getAll().subscribe({
      next:  d => { this.quotes.set(d); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  setFilter(s: QuoteStatus | 'all'): void { this.filterStatus.set(s); }

  goToNew():          void { this.router.navigate(['/quotes/new']); }
  goToDetail(q: Quote): void { this.router.navigate(['/quotes', q.uuid]); }
  goToEdit(q: Quote, e: Event): void {
    e.stopPropagation();
    this.router.navigate(['/quotes', q.uuid, 'edit']);
  }

  delete(q: Quote, e: Event): void {
    e.stopPropagation();
    if (!confirm(`Delete ${q.quote_number}?`)) return;
    this.quoteService.destroy(q.uuid).subscribe(() => this.load());
  }

  formatCurrency(v: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(v);
  }
}