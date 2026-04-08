// ============================================================
// invoice-list.component.ts — navigate with uuid
// ============================================================
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
    { label: 'Number' },
    { label: 'Client' },
    { label: 'Total', align: 'right' as const },
    { label: 'Due Date' },
    { label: 'Status' },
    { label: 'Actions' },
  ];

  readonly statuses: { value: InvoiceStatus | 'all'; label: string }[] = [
    { value: 'all',     label: 'All' },
    { value: 'unpaid',  label: 'Unpaid' },
    { value: 'paid',    label: 'Paid' },
    { value: 'overdue', label: 'Overdue' },
  ];

  filtered = computed(() => {
    const s = this.filterStatus();
    return s === 'all' ? this.invoices() : this.invoices().filter(i => i.status === s);
  });

  outstanding = computed(() =>
    this.invoices().filter(i => i.status !== 'paid').reduce((s, i) => s + i.total, 0)
  );

  ngOnInit(): void { this.load(); }

  load(): void {
    this.loading.set(true);
    this.invoiceService.getAll().subscribe({
      next:  d => { this.invoices.set(d); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  setFilter(s: InvoiceStatus | 'all'): void { this.filterStatus.set(s); }

  goToNew():            void { this.router.navigate(['/invoices/new']); }
  goToDetail(i: Invoice): void { this.router.navigate(['/invoices', i.uuid]); }
  goToEdit(i: Invoice, e: Event): void {
    e.stopPropagation();
    this.router.navigate(['/invoices', i.uuid, 'edit']);
  }

  delete(i: Invoice, e: Event): void {
    e.stopPropagation();
    if (confirm(`Are you sure you want to delete invoice #${i.invoice_number}?`)) {
      this.invoiceService.destroy(i.uuid).subscribe(() => this.load());
    }
  }
  

  formatCurrency(v: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(v);
  }

  isDueSoon(i: Invoice): boolean {
    return i.status === 'unpaid' && i.days_until_due <= 7 && i.days_until_due >= 0;
  }
}