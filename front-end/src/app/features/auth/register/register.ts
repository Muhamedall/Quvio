// ============================================================
// register.component.ts  — FIXED
//
// SAME FIXES AS login.component.ts:
//
// ❌ FIX 1 — NG2003: constructor DI → inject() function
// ❌ FIX 2 — TS2307: wrong import path '@core/auth/auth.service'
//                    → correct path '@core/auth' (barrel index)
// ❌ FIX 3 — TS7006: err implicitly any → HttpErrorResponse
// ============================================================

import { Component, inject }   from '@angular/core';
import { CommonModule }         from '@angular/common';
import { RouterLink, Router }   from '@angular/router';
import { HttpErrorResponse }    from '@angular/common/http';
import {
  ReactiveFormsModule,
  FormBuilder,
  FormGroup,
  Validators,
  AbstractControl,
  ValidationErrors,
} from '@angular/forms';

import { AuthService }                    from '../../../core/auth';
import { ButtonComponent, CardComponent } from '../../../shared/components';

// ── CUSTOM VALIDATOR ─────────────────────────────────────
// Pure function outside the class — checks password === confirm
// Return null = valid. Return object = error.
function passwordsMatch(control: AbstractControl): ValidationErrors | null {
  const pw  = control.get('password')?.value;
  const cpw = control.get('password_confirmation')?.value;
  if (pw && cpw && pw !== cpw) {
    return { passwordsMismatch: true };
  }
  return null;
}

@Component({
  selector:    'app-register',
  standalone:  true,
  imports:     [CommonModule, ReactiveFormsModule, RouterLink, ButtonComponent, CardComponent],
  templateUrl: './register.html',
})
export class RegisterComponent {

  // ── inject() replaces constructor DI ─────────────────
  private authService = inject(AuthService);
  private router      = inject(Router);
  private fb          = inject(FormBuilder);

  registerForm:  FormGroup;
  isLoading    = false;
  errorMessage = '';
  showPassword = false;
  showConfirm  = false;

  features = [
    'Create & send professional invoices',
    'Convert quotes to invoices in one click',
    'Get paid via Stripe payment links',
    'Automated reminders via email',
  ];

  constructor() {
    this.registerForm = this.fb.group(
      {
        name:                  ['', [Validators.required, Validators.minLength(2)]],
        email:                 ['', [Validators.required, Validators.email]],
        password:              ['', [Validators.required, Validators.minLength(8)]],
        password_confirmation: ['', Validators.required],
      },
      { validators: passwordsMatch }
    );
  }

  // Getters
  get name()    { return this.registerForm.get('name'); }
  get email()   { return this.registerForm.get('email'); }
  get password(){ return this.registerForm.get('password'); }
  get confirm() { return this.registerForm.get('password_confirmation'); }

  get passwordsMismatch(): boolean {
    return !!(this.confirm?.touched && this.registerForm.errors?.['passwordsMismatch']);
  }

  onSubmit(): void {
    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      return;
    }

    this.isLoading    = true;
    this.errorMessage = '';

    this.authService.register(this.registerForm.value).subscribe({
      next: () => {
        this.router.navigate(['/dashboard']);
      },
      // ── HttpErrorResponse fixes TS7006 implicit 'any' ──
      error: (err: HttpErrorResponse) => {
        const errors = err.error?.errors;
        if (errors) {
          this.errorMessage = (Object.values(errors) as string[][]).flat().join(' ');
        } else {
          this.errorMessage = err.error?.message || 'Registration failed. Please try again.';
        }
        this.isLoading = false;
      },
    });
  }
}