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
  load(uuid: string) {
    throw new Error('Method not implemented.');
  }

  print():  void { window.print(); }
  goBack(): void {
    const id = this.route.snapshot.paramMap.get('id');
    this.router.navigate(['/invoices', id]);
  }

  formatCurrency(v: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(v);
  }

  today(): string { return new Date().toLocaleDateString('fr-FR'); }
}