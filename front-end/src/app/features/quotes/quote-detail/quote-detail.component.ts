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

  quote        = signal<Quote | null>(null);
  loading      = signal(true);
  converting   = signal(false);
  sending      = signal(false);
  showConfirm  = signal(false);
  actionMsg    = signal('');

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.load(id);
  }

  load(id: number): void {
    this.loading.set(true);
    this.quoteService.getById(id).subscribe({
      next:  (data) => { this.quote.set(data); this.loading.set(false); },
      error: ()     => { this.loading.set(false); },
    });
  }

  // Send quote to client via n8n
  sendQuote(): void {
    const q = this.quote();
    if (!q) return;
    this.sending.set(true);
    this.quoteService.send(q.id).subscribe({
      next: () => {
        this.sending.set(false);
        this.actionMsg.set('Quote sent to client successfully!');
        this.load(q.id);
        setTimeout(() => this.actionMsg.set(''), 4000);
      },
      error: () => this.sending.set(false),
    });
  }

  // Convert quote → invoice
  convertToInvoice(): void {
    const q = this.quote();
    if (!q) return;
    this.converting.set(true);
    this.showConfirm.set(false);
    this.quoteService.convert(q.id).subscribe({
      next: (res) => {
        this.converting.set(false);
        // Navigate to the new invoice
        this.router.navigate(['/invoices', res.invoice.id]);
      },
      error: () => this.converting.set(false),
    });
  }

  goToEdit(): void {
    const q = this.quote();
    if (q) this.router.navigate(['/quotes', q.id, 'edit']);
  }

  goToPreview(): void {
    const q = this.quote();
    if (q) this.router.navigate(['/quotes', q.id, 'preview']);
  }

  goBack(): void {
    this.router.navigate(['/quotes']);
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  }

 get canSend(): boolean {
  const s = this.quote()?.status;
  return s === 'draft' || s === 'sent';
}
  get canConvert(): boolean {
    const s = this.quote()?.status;
    return (s === 'sent' || s === 'approved' || s === 'draft') && !this.quote()?.is_converted;
  }

  
}