import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }    from '@angular/common';
import { Router, ActivatedRoute, RouterLink } from '@angular/router';
import { InvoiceService }  from '../../../core/services';
import { Invoice }         from '../../../core/models';
import {
  ButtonComponent, BadgeComponent, CardComponent,
  LoaderComponent, ModalComponent,
} from '../../../shared/components';

@Component({
  selector:    'app-invoice-detail',
  standalone:  true,
  imports:     [CommonModule, RouterLink, ButtonComponent, BadgeComponent,
                CardComponent, LoaderComponent, ModalComponent],
  templateUrl: './invoice-detail.component.html',
})
export class InvoiceDetailComponent implements OnInit {

  private invoiceService = inject(InvoiceService);
  private router         = inject(Router);
  private route          = inject(ActivatedRoute);

  invoice         = signal<Invoice | null>(null);
  loading         = signal(true);
  generatingLink  = signal(false);
  sending         = signal(false);
  downloading     = signal(false);
  actionMsg       = signal('');
  actionError     = signal('');

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.load(id);
  }

  load(id: number): void {
    this.loading.set(true);
    this.invoiceService.getById(id).subscribe({
      next:  (data) => { this.invoice.set(data); this.loading.set(false); },
      error: ()     => this.loading.set(false),
    });
  }

  // Generate Stripe payment link
  generatePaymentLink(): void {
    const inv = this.invoice();
    if (!inv) return;
    this.generatingLink.set(true);
    this.actionError.set('');
    this.invoiceService.generatePaymentLink(inv.id).subscribe({
      next: (res) => {
        this.generatingLink.set(false);
        this.actionMsg.set('Payment link generated!');
        this.load(inv.id); // reload to get stripe_link
        setTimeout(() => this.actionMsg.set(''), 4000);
      },
      error: (err) => {
        this.generatingLink.set(false);
        this.actionError.set(err.error?.message ?? 'Failed to generate payment link.');
      },
    });
  }

  // Send invoice via n8n
  sendInvoice(): void {
    const inv = this.invoice();
    if (!inv) return;
    this.sending.set(true);
    this.invoiceService.send(inv.id).subscribe({
      next: () => {
        this.sending.set(false);
        this.actionMsg.set('Invoice sent to client!');
        setTimeout(() => this.actionMsg.set(''), 4000);
      },
      error: () => this.sending.set(false),
    });
  }

  // Download PDF
  downloadPdf(): void {
    const inv = this.invoice();
    if (!inv) return;
    this.downloading.set(true);
    this.invoiceService.downloadPdf(inv.id).subscribe({
      next: (blob: Blob) => {
        const url  = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href     = url;
        link.download = `invoice-${inv.invoice_number}.pdf`;
        link.click();
        window.URL.revokeObjectURL(url);
        this.downloading.set(false);
      },
      error: () => this.downloading.set(false),
    });
  }

  // Copy Stripe link to clipboard
  copyLink(): void {
    const link = this.invoice()?.stripe_link;
    if (!link) return;
    navigator.clipboard.writeText(link).then(() => {
      this.actionMsg.set('Payment link copied to clipboard!');
      setTimeout(() => this.actionMsg.set(''), 3000);
    });
  }

  goToEdit():    void { this.router.navigate(['/invoices', this.invoice()!.id, 'edit']); }
  goToPreview(): void { this.router.navigate(['/invoices', this.invoice()!.id, 'preview']); }
  goBack():      void { this.router.navigate(['/invoices']); }

  formatCurrency(v: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(v);
  }

  get isPaid():    boolean { return this.invoice()?.status === 'paid'; }
  get isOverdue(): boolean { return this.invoice()?.status === 'overdue'; }
  get canEdit():   boolean { return !this.isPaid; }
}