import {
  Component, inject, OnInit, signal, computed
} from '@angular/core';
import { CommonModule }    from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import {
  ReactiveFormsModule, FormBuilder, FormGroup,
  FormArray, Validators, AbstractControl,
} from '@angular/forms';
import { QuoteService, ClientService } from '../../../core/services';
import { Client, Quote }               from '../../../core/models';
import { ButtonComponent, CardComponent, LoaderComponent } from '../../../shared/components';

@Component({
  selector:    'app-quote-form',
  standalone:  true,
  imports:     [CommonModule, ReactiveFormsModule, ButtonComponent, CardComponent, LoaderComponent],
  templateUrl: './quote-form.component.html',
})
export class QuoteFormComponent implements OnInit {

  private fb           = inject(FormBuilder);
  private quoteService = inject(QuoteService);
  private clientService = inject(ClientService);
  private router       = inject(Router);
  private route        = inject(ActivatedRoute);

  clients  = signal<Client[]>([]);
  loading  = signal(false);
  fetching = signal(false); // fetching existing quote for edit
  error    = signal('');
  quoteId  = signal<number | null>(null);

  form!: FormGroup;

  // Computed totals — update live as user types
  subtotal = computed(() => {
    const items = this.form?.get('items') as FormArray;
    if (!items) return 0;
    return items.controls.reduce((sum, ctrl) => {
      const qty   = Number(ctrl.get('quantity')?.value  || 0);
      const price = Number(ctrl.get('unit_price')?.value || 0);
      return sum + (qty * price);
    }, 0);
  });

  taxAmount = computed(() => {
    const rate = Number(this.form?.get('tax_rate')?.value || 0);
    return this.subtotal() * (rate / 100);
  });

  total = computed(() => this.subtotal() + this.taxAmount());

  get isEdit(): boolean { return !!this.quoteId(); }
  get items(): FormArray { return this.form.get('items') as FormArray; }

  ngOnInit(): void {
    this.buildForm();
    this.loadClients();

    // Check if editing
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.quoteId.set(Number(id));
      this.loadQuote(Number(id));
    }
  }

  buildForm(): void {
    this.form = this.fb.group({
      client_id:   ['', Validators.required],
      tax_rate:    [20],
      notes:       [''],
      valid_until: [''],
      items:       this.fb.array([this.newItem()]),
    });
  }

  newItem(): FormGroup {
    return this.fb.group({
      description: ['', Validators.required],
      quantity:    [1, [Validators.required, Validators.min(0.01)]],
      unit_price:  [0, [Validators.required, Validators.min(0)]],
    });
  }

  addItem(): void {
    this.items.push(this.newItem());
  }

  removeItem(index: number): void {
    if (this.items.length > 1) this.items.removeAt(index);
  }

  loadClients(): void {
    this.clientService.getAll().subscribe({
      next: (data) => this.clients.set(data),
    });
  }

  loadQuote(id: number): void {
    this.fetching.set(true);
    this.quoteService.getById(id).subscribe({
      next: (quote: Quote) => {
        // Clear default items
        while (this.items.length) this.items.removeAt(0);

        // Patch form values
        this.form.patchValue({
          client_id:   quote.client?.id ?? '',
          tax_rate:    quote.tax_rate,
          notes:       quote.notes ?? '',
          valid_until: quote.valid_until ?? '',
        });

        // Add existing items
        (quote.items ?? []).forEach(item => {
          this.items.push(this.fb.group({
            description: [item.description, Validators.required],
            quantity:    [item.quantity, [Validators.required, Validators.min(0.01)]],
            unit_price:  [item.unit_price, [Validators.required, Validators.min(0)]],
          }));
        });

        this.fetching.set(false);
      },
      error: () => this.fetching.set(false),
    });
  }

  onSubmit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }

    this.loading.set(true);
    this.error.set('');

    const payload = this.form.value;

    const action$ = this.isEdit
      ? this.quoteService.patch(this.quoteId()!, payload)
      : this.quoteService.store(payload);

    action$.subscribe({
      next: (quote: Quote) => {
        this.loading.set(false);
        this.router.navigate(['/quotes', quote.id]);
      },
      error: (err: HttpErrorResponse) => {
        this.error.set(err.error?.message ?? 'Failed to save quote.');
        this.loading.set(false);
      },
    });
  }

  cancel(): void {
    this.router.navigate(['/quotes']);
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  }

  itemSubtotal(ctrl: AbstractControl): number {
    const qty   = Number(ctrl.get('quantity')?.value  || 0);
    const price = Number(ctrl.get('unit_price')?.value || 0);
    return qty * price;
  }
}