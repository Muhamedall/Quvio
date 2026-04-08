import {
  Component, inject, Input, Output, EventEmitter,
  OnInit, OnChanges, SimpleChanges, ChangeDetectionStrategy,
  ChangeDetectorRef,
} from '@angular/core';
import { CommonModule }    from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import {
  ReactiveFormsModule, FormBuilder,
  FormGroup, Validators,
} from '@angular/forms';
import { ClientService } from '../../../core/services';
import { Client }        from '../../../core/models';
import { ButtonComponent } from '../../../shared/components';

@Component({
  selector:        'app-client-form',
  standalone:      true,
  imports:         [CommonModule, ReactiveFormsModule, ButtonComponent],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl:     './client-form.html',
})
export class ClientFormComponent implements OnInit, OnChanges {

  // null = create mode | Client object = edit mode
  @Input()  client:    Client | null = null;
  @Output() saved     = new EventEmitter<void>();
  @Output() cancelled = new EventEmitter<void>();

  private clientService = inject(ClientService);
  private fb            = inject(FormBuilder);
  private cdr           = inject(ChangeDetectorRef);

  form!:    FormGroup;
  loading = false;
  error   = '';

  get isEdit(): boolean { return !!this.client; }

  ngOnInit(): void {
    this.buildForm();
  }

  // ── KEY FIX: OnChanges runs when @Input client changes ──
  // When modal opens for EDIT, Angular passes the client object.
  // Without OnChanges the form keeps the previous values.
  ngOnChanges(changes: SimpleChanges): void {
    if (changes['client'] && this.form) {
      this.patchForm();
      this.error = '';
      this.cdr.markForCheck();
    }
  }

  buildForm(): void {
    this.form = this.fb.group({
      name:    ['', [Validators.required, Validators.minLength(2)]],
      email:   ['', [Validators.required, Validators.email]],
      phone:   [''],
      company: [''],
      address: [''],
    });
    // Patch immediately if client already set (edit mode on init)
    this.patchForm();
  }

  patchForm(): void {
    if (this.client) {
      // setValue fills ALL fields — null → empty string for optional fields
      this.form.setValue({
        name:    this.client.name    ?? '',
        email:   this.client.email   ?? '',
        phone:   this.client.phone   ?? '',
        company: this.client.company ?? '',
        address: this.client.address ?? '',
      });
    } else {
      // Reset to empty for create mode
      this.form.reset({ name: '', email: '', phone: '', company: '', address: '' });
    }
  }

  onSubmit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }

    this.loading = true;
    this.error   = '';

    const action$ = this.isEdit
      ? this.clientService.patch(this.client!.id, this.form.value)
      : this.clientService.store(this.form.value);

    action$.subscribe({
      next:  () => { this.loading = false; this.saved.emit(); },
      error: (err: HttpErrorResponse) => {
        this.error   = err.error?.message ?? 'Failed to save client.';
        this.loading = false;
        this.cdr.markForCheck();
      },
    });
  }
}