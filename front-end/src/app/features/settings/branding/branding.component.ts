import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }    from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { HttpErrorResponse }            from '@angular/common/http';
import {
  ReactiveFormsModule, FormBuilder,
  FormGroup, Validators,
} from '@angular/forms';
import { SettingsService, BrandingSettings } from '../settings.service';
import { ButtonComponent, CardComponent, LoaderComponent } from '../../../shared/components';

@Component({
  selector:    'app-branding',
  standalone:  true,
  imports:     [CommonModule, RouterLink, RouterLinkActive,
                ReactiveFormsModule, ButtonComponent, CardComponent, LoaderComponent],
  templateUrl: './branding.component.html',
})
export class BrandingComponent implements OnInit {

  private settingsService = inject(SettingsService);
  private fb              = inject(FormBuilder);

  form!:   FormGroup;
  loading  = signal(true);
  saving   = signal(false);
  msg      = signal('');
  error    = signal('');

  ngOnInit(): void {
    this.form = this.fb.group({
      company_name:    [''],
      company_address: [''],
      company_phone:   [''],
      company_email:   ['', [Validators.email]],
      company_website: [''],
      invoice_notes:   [''],
      invoice_prefix:  ['INV'],
      quote_prefix:    ['QUO'],
    });

    this.settingsService.getBranding().subscribe({
      next: (res) => {
        this.form.patchValue(res.branding);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  onSubmit(): void {
    if (this.form.invalid) return;
    this.saving.set(true);
    this.msg.set('');
    this.error.set('');

    this.settingsService.updateBranding(this.form.value).subscribe({
      next: () => {
        this.saving.set(false);
        this.msg.set('Branding saved successfully!');
        setTimeout(() => this.msg.set(''), 4000);
      },
      error: (err: HttpErrorResponse) => {
        this.error.set(err.error?.message ?? 'Failed to save branding.');
        this.saving.set(false);
      },
    });
  }
}