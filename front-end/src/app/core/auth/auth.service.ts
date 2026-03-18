// ============================================================
// auth.service.ts
// ============================================================

import { Injectable, signal, inject } from '@angular/core';
import { HttpClient }                  from '@angular/common/http';
import { Router }                      from '@angular/router';
import { tap }                         from 'rxjs/operators';
import { Observable }                  from 'rxjs';
import { environment }                 from '../../../environments/environment';
import {
  LoginPayload,
  RegisterPayload,
  AuthResponse,
  User,
} from '../models';

@Injectable({ providedIn: 'root' })
export class AuthService {

  // inject() — consistent with the rest of the app
  private http   = inject(HttpClient);
  private router = inject(Router);

  currentUser = signal<User | null>(null);

  private apiUrl = environment.apiUrl;

  constructor() {
    // Restore session on app startup
    this.loadUserFromStorage();
  }

  login(payload: LoginPayload): Observable<AuthResponse> {
    return this.http
      .post<AuthResponse>(`${this.apiUrl}/auth/login`, payload)
      .pipe(tap((res) => this.saveSession(res)));
  }

  register(payload: RegisterPayload): Observable<AuthResponse> {
    return this.http
      .post<AuthResponse>(`${this.apiUrl}/auth/register`, payload)
      .pipe(tap((res) => this.saveSession(res)));
  }

  logout(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    this.currentUser.set(null);
    this.router.navigate(['/auth/login']);
  }

  isAuthenticated(): boolean {
    return !!localStorage.getItem('token');
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  private saveSession(res: AuthResponse): void {
    localStorage.setItem('token', res.token);
    localStorage.setItem('user', JSON.stringify(res.user));
    this.currentUser.set(res.user);
  }

  private loadUserFromStorage(): void {
    const userJson = localStorage.getItem('user');
    if (userJson) {
      try {
        this.currentUser.set(JSON.parse(userJson));
      } catch {
        localStorage.removeItem('user');
      }
    }
  }
}