import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }                       from '@angular/common';
import { Router, ActivatedRoute }             from '@angular/router';
import { InvoiceService }                     from '../../../core/services';
import { Invoice }                            from '../../../core/models';
import { LoaderComponent, ButtonComponent }   from '../../../shared/components';

@Component({
  selector:    'app-invoice-preview',
  standalone:  true,
  imports:     [CommonModule, LoaderComponent, ButtonComponent],
  templateUrl: './invoice-preview.component.html',
})
export class InvoicePreviewComponent implements OnInit {

  private invoiceService = inject(InvoiceService);
  private router         = inject(Router);
  private route          = inject(ActivatedRoute);

  invoice = signal<Invoice | null>(null);
  loading = signal(true);

  ngOnInit(): void {
    const uuid = this.route.snapshot.paramMap.get('id');
    if (uuid) {
      this.load(uuid);
    } else {
      this.loading.set(false);
    }
  }

  // ✅ Correct implementation of load
  load(uuid: string) {
    this.loading.set(true);
    this.invoiceService.getById(uuid).subscribe({
      next: (inv: Invoice) => {
        this.invoice.set(inv);
        this.loading.set(false);
      },
      error: (err) => {
        console.error('Failed to load invoice', err);
        this.loading.set(false);
      }
    });
  }

  print(): void { window.print(); }

  goBack(): void {
    // Navigate back to invoices list
    this.router.navigate(['/invoices']);
  }

  formatCurrency(v: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(v);
  }

  today(): string {
    return new Date().toLocaleDateString('fr-FR');
  }
}