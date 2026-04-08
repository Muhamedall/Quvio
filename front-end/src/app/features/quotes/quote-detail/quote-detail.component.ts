import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }    from '@angular/common';
import { Router, ActivatedRoute, RouterLink } from '@angular/router';
import { QuoteService }    from '../../../core/services';
import { Quote }           from '../../../core/models';
import {
  ButtonComponent, BadgeComponent, CardComponent,
  LoaderComponent, ModalComponent,
} from '../../../shared/components';

@Component({
  selector:    'app-quote-detail',
  standalone:  true,
  imports:     [CommonModule, RouterLink, ButtonComponent, BadgeComponent,
                CardComponent, LoaderComponent, ModalComponent],
  templateUrl: './quote-detail.component.html',
})
export class QuoteDetailComponent implements OnInit {

  private quoteService = inject(QuoteService);
  private router       = inject(Router);
  private route        = inject(ActivatedRoute);

  quote       = signal<Quote | null>(null);
  loading     = signal(true);
  converting  = signal(false);
  sending     = signal(false);
  showConfirm = signal(false);
  actionMsg   = signal('');
  actionError = signal('');

  // uuid from route param e.g. /quotes/abc-123
  private get uuid(): string {
    return this.route.snapshot.paramMap.get('id') ?? '';
  }

  ngOnInit(): void { this.load(); }

  load(): void {
    this.loading.set(true);
    this.quoteService.getById(this.uuid).subscribe({
      next:  d => { this.quote.set(d); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  sendQuote(): void {
    const q = this.quote(); if (!q) return;
    this.sending.set(true);
    this.actionError.set('');
    this.quoteService.send(q.uuid).subscribe({
      next: () => {
        this.sending.set(false);
        this.actionMsg.set('Quote sent to client successfully!');
        this.load();
        setTimeout(() => this.actionMsg.set(''), 4000);
      },
      error: err => {
        this.sending.set(false);
        this.actionError.set(err.error?.message ?? 'Failed to send quote.');
      },
    });
  }

  convertToInvoice(): void {
    const q = this.quote(); if (!q) return;
    this.converting.set(true);
    this.showConfirm.set(false);
    this.actionError.set('');

    this.quoteService.convert(q.uuid).subscribe({
      next: res => {
        this.converting.set(false);
        // Navigate using uuid of new invoice
        const invoiceUuid = res?.invoice?.uuid;
        if (invoiceUuid) {
          this.router.navigate(['/invoices', invoiceUuid]);
        } else {
          this.router.navigate(['/invoices']);
        }
      },
      error: err => {
        this.converting.set(false);
        this.actionError.set(err.error?.message ?? 'Failed to convert quote.');
        if (err.status === 422) {
          setTimeout(() => this.router.navigate(['/invoices']), 2000);
        }
      },
    });
  }

  goToEdit():    void { const q = this.quote(); if (q) this.router.navigate(['/quotes', q.uuid, 'edit']); }
  goToPreview(): void { const q = this.quote(); if (q) this.router.navigate(['/quotes', q.uuid, 'preview']); }
  goBack():      void { this.router.navigate(['/quotes']); }

  formatCurrency(v: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(v);
  }

  get canSend():    boolean { const s = this.quote()?.status; return s === 'draft' || s === 'sent'; }
  get canConvert(): boolean {
    const s = this.quote()?.status;
    return (s === 'sent' || s === 'approved' || s === 'draft') && !this.quote()?.is_converted;
  }
}