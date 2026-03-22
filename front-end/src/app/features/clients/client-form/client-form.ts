import { Component, inject, Input, Output, EventEmitter, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule }              from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpErrorResponse }         from '@angular/common/http';
import { ClientService }             from '../../../core/services';
import { Client }                    from '../../../core/models';
import { ButtonComponent }           from '../../../shared/components';

@Component({
  selector:        'app-client-form',
  standalone:      true,
  imports:         [CommonModule, ReactiveFormsModule, ButtonComponent],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl:     './client-form.html',
})
export class ClientFormComponent implements OnInit {

  @Input()  client: Client | null = null;  // null = create mode, Client = edit mode
  @Output() saved     = new EventEmitter<void>();
  @Output() cancelled = new EventEmitter<void>();

  private clientService = inject(ClientService);
  private fb            = inject(FormBuilder);

  form!:     FormGroup;
  loading  = false;
  error    = '';

  ngOnInit(): void {
    this.form = this.fb.group({
      name:    [this.client?.name    ?? '', [Validators.required, Validators.minLength(2)]],
      email:   [this.client?.email   ?? '', [Validators.required, Validators.email]],
      phone:   [this.client?.phone   ?? ''],
      company: [this.client?.company ?? ''],
      address: [this.client?.address ?? ''],
    });
  }

  get isEdit(): boolean { return !!this.client; }

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
      },
    });
  }
}