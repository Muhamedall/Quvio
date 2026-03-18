// ============================================================
// login.component.ts  — FIXED
//
// ERRORS FIXED:
//
// ❌ ERROR 1 — NG2003: No suitable injection token for 'authService'
//    CAUSE:  constructor(private authService: AuthService)
//            Angular DI can't resolve a class imported from a barrel file
//            via constructor injection in some configurations.
//    FIX:    Use inject() function — the modern Angular 17+ way.
//            inject() works reliably with barrel imports.
//
//    BEFORE (broken):
//      constructor(private authService: AuthService) {}
//
//    AFTER (fixed):
//      private authService = inject(AuthService);
//
// ❌ ERROR 2 — TS7006: Parameter 'err' implicitly has an 'any' type
//    CAUSE:  error: (err) => { ... }
//            strict: true in tsconfig.json forbids implicit 'any'
//    FIX:    Type the error explicitly as HttpErrorResponse
//
//    BEFORE (broken):  error: (err) => {
//    AFTER  (fixed):   error: (err: HttpErrorResponse) => {
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
} from '@angular/forms';

import { AuthService }                   from '../../../core/auth/auth.service';
import { ButtonComponent, CardComponent } from '../../../components';

@Component({
  selector:    'app-login',
  standalone:  true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    ButtonComponent,
    CardComponent,
  ],
  templateUrl: './login.html',
})
export class LoginComponent {

  // ── inject() replaces constructor DI ─────────────────
  // inject() is the Angular 17+ functional injection API.
  // It works reliably with barrel file imports and standalone components.
  // No constructor parameter needed.
  private authService = inject(AuthService);
  private router      = inject(Router);
  private fb          = inject(FormBuilder);

  // Form, state
  loginForm:    FormGroup;
  isLoading    = false;
  errorMessage = '';
  showPassword = false;

  constructor() {
    // Constructor is now clean — only form setup
    this.loginForm = this.fb.group({
      email:    ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
    });
  }

  // Getters — shortcuts for the template
  get email()    { return this.loginForm.get('email'); }
  get password() { return this.loginForm.get('password'); }

  onSubmit(): void {
    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    this.isLoading    = true;
    this.errorMessage = '';

    this.authService.login(this.loginForm.value).subscribe({
      next: () => {
        this.router.navigate(['/dashboard']);
      },
      // ── HttpErrorResponse types the error explicitly ──
      // Fixes: TS7006 Parameter 'err' implicitly has an 'any' type
      error: (err: HttpErrorResponse) => {
        this.errorMessage =
          err.error?.message || 'Invalid credentials. Please try again.';
        this.isLoading = false;
      },
    });
  }
}