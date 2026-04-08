import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }    from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { HttpErrorResponse }            from '@angular/common/http';
import {
  ReactiveFormsModule, FormBuilder,
  FormGroup, Validators,
} from '@angular/forms';
import { AuthService }     from '../../../core/auth/auth.service';
import { SettingsService } from '../settings.service';
import { ButtonComponent, CardComponent } from '../../../shared/components';

@Component({
  selector:    'app-profile',
  standalone:  true,
  imports:     [CommonModule, RouterLink, RouterLinkActive,
                ReactiveFormsModule, ButtonComponent, CardComponent],
  templateUrl: './profile.component.html',
})
export class ProfileComponent implements OnInit {

  private settingsService = inject(SettingsService);
  private authService     = inject(AuthService);
  private fb              = inject(FormBuilder);

  profileForm!:  FormGroup;
  passwordForm!: FormGroup;

  savingProfile  = signal(false);
  savingPassword = signal(false);
  profileMsg     = signal('');
  profileError   = signal('');
  passwordMsg    = signal('');
  passwordError  = signal('');
  showPassword   = signal(false);

  ngOnInit(): void {
    const user = this.authService.currentUser();

    this.profileForm = this.fb.group({
      name:  [user?.name  ?? '', [Validators.required, Validators.minLength(2)]],
      email: [user?.email ?? '', [Validators.required, Validators.email]],
    });

    this.passwordForm = this.fb.group({
      current_password:      ['', Validators.required],
      password:              ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required],
    });
  }

  saveProfile(): void {
    if (this.profileForm.invalid) { this.profileForm.markAllAsTouched(); return; }

    this.savingProfile.set(true);
    this.profileMsg.set('');
    this.profileError.set('');

    this.settingsService.updateProfile(this.profileForm.value).subscribe({
      next: (res) => {
        this.savingProfile.set(false);
        this.profileMsg.set('Profile updated successfully!');

        // ── KEY FIX: update AuthService signal immediately ──
        // Without this, the sidebar/navbar keep showing old name
        // until logout + login refreshes the token data.
        // We update the in-memory signal directly so all
        // components using currentUser() reflect the change now.
        const current = this.authService.currentUser();
        if (current) {
          this.authService.setCurrentUser({
            ...current,
            name:  res.user.name,
            email: res.user.email,
          });
        }

        setTimeout(() => this.profileMsg.set(''), 4000);
      },
      error: (err: HttpErrorResponse) => {
        this.profileError.set(err.error?.message ?? 'Failed to update profile.');
        this.savingProfile.set(false);
      },
    });
  }

  savePassword(): void {
    if (this.passwordForm.invalid) { this.passwordForm.markAllAsTouched(); return; }

    const { password, password_confirmation } = this.passwordForm.value;
    if (password !== password_confirmation) {
      this.passwordError.set('Passwords do not match.');
      return;
    }

    this.savingPassword.set(true);
    this.passwordMsg.set('');
    this.passwordError.set('');

    this.settingsService.updatePassword(this.passwordForm.value).subscribe({
      next: () => {
        this.savingPassword.set(false);
        this.passwordMsg.set('Password changed successfully!');
        this.passwordForm.reset();
        setTimeout(() => this.passwordMsg.set(''), 4000);
      },
      error: (err: HttpErrorResponse) => {
        this.passwordError.set(err.error?.message ?? 'Failed to change password.');
        this.savingPassword.set(false);
      },
    });
  }
}