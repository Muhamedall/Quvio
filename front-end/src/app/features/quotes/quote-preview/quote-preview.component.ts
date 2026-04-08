import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }                       from '@angular/common';
import { Router, ActivatedRoute }             from '@angular/router';
import { QuoteService }                       from '../../../core/services';
import { Quote }                              from '../../../core/models';
import { LoaderComponent, ButtonComponent }   from '../../../shared/components';

@Component({
  selector:    'app-quote-preview',
  standalone:  true,
  imports:     [CommonModule, LoaderComponent, ButtonComponent],
  templateUrl: './quote-preview.component.html',
})
export class QuotePreviewComponent implements OnInit {

  private quoteService = inject(QuoteService);
  private router       = inject(Router);
  private route        = inject(ActivatedRoute);

  quote   = signal<Quote | null>(null);
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
    this.quoteService.getById(uuid).subscribe({
      next: (q: Quote) => {
        this.quote.set(q);
        this.loading.set(false);
      },
      error: (err) => {
        console.error('Failed to load quote', err);
        this.loading.set(false);
      }
    });
  }

  print(): void { window.print(); }

  goBack(): void {
    this.router.navigate(['/quotes']); // safer than using the id
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  }

  today(): string {
    return new Date().toLocaleDateString('fr-FR');
  }
}