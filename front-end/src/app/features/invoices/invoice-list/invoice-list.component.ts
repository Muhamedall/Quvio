import { Component, inject, OnInit, signal, computed } from '@angular/core';
import { CommonModule }       from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { InvoiceService }     from '../../../core/services';
import { Invoice, InvoiceStatus } from '../../../core/models';
import {
  ButtonComponent, BadgeComponent, TableComponent,
  LoaderComponent, EmptyStateComponent,
} from '../../../shared/components';

@Component({
  selector:    'app-invoice-list',
  standalone:  true,
  imports:     [CommonModule, RouterLink, ButtonComponent, BadgeComponent,
                TableComponent, LoaderComponent, EmptyStateComponent],
  templateUrl: './invoice-list.component.html',
})
export class InvoiceListComponent implements OnInit {

  private invoiceService = inject(InvoiceService);
  private router         = inject(Router);

  invoices     = signal<Invoice[]>([]);
  loading      = signal(true);
  filterStatus = signal<InvoiceStatus | 'all'>('all');

  readonly columns = [
    { label: 'Number'  },
    { label: 'Client'  },
    { label: 'Total',  align: 'right' as const },
    { label: 'Due Date' },
    { label: 'Status'  },
    { label: 'Actions' },
  ];

  readonly statuses: { value: InvoiceStatus | 'all'; label: string; color: string }[] = [
    { value: 'all',     label: 'All',     color: '' },
    { value: 'unpaid',  label: 'Unpaid',  color: 'text-amber-600' },
    { value: 'paid',    label: 'Paid',    color: 'text-emerald-600' },
    { value: 'overdue', label: 'Overdue', color: 'text-red-600' },
  ];

  filtered = computed(() => {
    const status = this.filterStatus();
    if (status === 'all') return this.invoices();
    return this.invoices().filter(i => i.status === status);
  });

  // Total outstanding amount
  outstanding = computed(() =>
    this.invoices()
      .filter(i => i.status !== 'paid')
      .reduce((sum, i) => sum + i.total, 0)
  );

  ngOnInit(): void { this.load(); }

  load(): void {
    this.loading.set(true);
    this.invoiceService.getAll().subscribe({
      next:  (data) => { this.invoices.set(data); this.loading.set(false); },
      error: ()     => this.loading.set(false),
    });
  }

  setFilter(status: InvoiceStatus | 'all'): void {
    this.filterStatus.set(status);
  }

  goToNew():          void { this.router.navigate(['/invoices/new']); }
  goToDetail(id: number): void { this.router.navigate(['/invoices', id]); }

  goToEdit(id: number, event: Event): void {
    event.stopPropagation();
    this.router.navigate(['/invoices', id, 'edit']);
  }

  delete(invoice: Invoice, event: Event): void {
    event.stopPropagation();
    if (invoice.status === 'paid') return;
    if (!confirm(`Delete ${invoice.invoice_number}?`)) return;
    this.invoiceService.destroy(invoice.id).subscribe(() => this.load());
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  }

  isDueSoon(invoice: Invoice): boolean {
    return invoice.status === 'unpaid' && invoice.days_until_due <= 7 && invoice.days_until_due >= 0;
  }
}