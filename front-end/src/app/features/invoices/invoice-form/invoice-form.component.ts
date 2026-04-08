import {
  Component, inject, OnInit, signal
} from '@angular/core';
import { CommonModule }           from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { HttpErrorResponse }      from '@angular/common/http';
import {
  ReactiveFormsModule, FormBuilder, FormGroup,
  FormArray, Validators, AbstractControl,
} from '@angular/forms';
import { InvoiceService, ClientService } from '../../../core/services';
import { Client, Invoice }               from '../../../core/models';
import { ButtonComponent, CardComponent, LoaderComponent } from '../../../shared/components';

@Component({
  selector:    'app-invoice-form',
  standalone:  true,
  imports:     [CommonModule, ReactiveFormsModule, ButtonComponent,
                CardComponent, LoaderComponent],
  templateUrl: './invoice-form.component.html',
})
export class InvoiceFormComponent implements OnInit {

  private fb             = inject(FormBuilder);
  private invoiceService = inject(InvoiceService);
  private clientService  = inject(ClientService);
  private router         = inject(Router);
  private route          = inject(ActivatedRoute);

  clients  = signal<Client[]>([]);
  loading  = signal(false);
  fetching = signal(false);
  error    = signal('');
  editUuid = signal<string | null>(null);

  form!: FormGroup;

  get subtotal(): number {
    const items = this.form?.get('items') as FormArray;
    if (!items) return 0;
    return items.controls.reduce((sum, ctrl) =>
      sum + Number(ctrl.get('quantity')?.value  || 0)
          * Number(ctrl.get('unit_price')?.value || 0), 0);
  }
  get taxRate():   number { return Number(this.form?.get('tax_rate')?.value || 0); }
  get taxAmount(): number { return this.subtotal * (this.taxRate / 100); }
  get total():     number { return this.subtotal + this.taxAmount; }
  get isEdit():    boolean   { return !!this.editUuid(); }
  get items():     FormArray { return this.form.get('items') as FormArray; }

  private defaultDueDate(): string {
    const d = new Date();
    d.setDate(d.getDate() + 30);
    return d.toISOString().split('T')[0];
  }

  ngOnInit(): void {
    this.buildForm();
    this.loadClients();
    const uuid = this.route.snapshot.paramMap.get('id');
    if (uuid) { this.editUuid.set(uuid); this.loadInvoice(uuid); }
  }

  buildForm(): void {
    this.form = this.fb.group({
      client_id: ['', Validators.required],
      tax_rate:  [20],
      due_date:  [this.defaultDueDate(), Validators.required],
      notes:     [''],
      items:     this.fb.array([this.newItem()]),
    });
  }

  newItem(): FormGroup {
    return this.fb.group({
      description: ['', Validators.required],
      quantity:    [1,  [Validators.required, Validators.min(0.01)]],
      unit_price:  [0,  [Validators.required, Validators.min(0)]],
    });
  }

  addItem():             void { this.items.push(this.newItem()); }
  removeItem(i: number): void { if (this.items.length > 1) this.items.removeAt(i); }

  loadClients(): void {
    this.clientService.getAll().subscribe({ next: d => this.clients.set(d) });
  }

  loadInvoice(uuid: string): void {
    this.fetching.set(true);
    this.invoiceService.getById(uuid).subscribe({
      next: (inv: Invoice) => {
        while (this.items.length) this.items.removeAt(0);
        this.form.patchValue({
          client_id: inv.client?.id ?? '',
          tax_rate:  inv.tax_rate,
          due_date:  inv.due_date,
          notes:     inv.notes ?? '',
        });
        (inv.items ?? []).forEach(item => {
          this.items.push(this.fb.group({
            description: [item.description, Validators.required],
            quantity:    [item.quantity,    [Validators.required, Validators.min(0.01)]],
            unit_price:  [item.unit_price,  [Validators.required, Validators.min(0)]],
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

    const action$ = this.isEdit
      ? this.invoiceService.patch(this.editUuid()!, this.form.value)
      : this.invoiceService.store(this.form.value);

    action$.subscribe({
      next: (inv: Invoice) => {
        this.loading.set(false);
        this.router.navigate(['/invoices', inv.uuid]);
      },
      error: (err: HttpErrorResponse) => {
        this.error.set(err.error?.message ?? 'Failed to save invoice.');
        this.loading.set(false);
      },
    });
  }

  cancel(): void { this.router.navigate(['/invoices']); }

  formatCurrency(v: number): string {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(v);
  }

  itemSubtotal(ctrl: AbstractControl): number {
    return Number(ctrl.get('quantity')?.value || 0)
         * Number(ctrl.get('unit_price')?.value || 0);
  }
}