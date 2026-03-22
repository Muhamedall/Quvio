// ============================================================
// api.service.ts — Base HTTP service
//
// All feature services extend this.
// Provides: base URL, response unwrapping, error handling.
// ============================================================

import { inject }                               from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { Observable, throwError }               from 'rxjs';
import { catchError, map }                      from 'rxjs/operators';
import { environment }                          from '../../../environments/environment';
import { ApiCollection, ApiResource }           from '../models';

export abstract class ApiService {

  protected http    = inject(HttpClient);
  protected baseUrl = environment.apiUrl;

  // ── GET collection → unwraps { data: [...] } ─────────
  protected fetchAll<T>(
    path: string,
    params?: Record<string, string>
  ): Observable<T[]> {
    let httpParams = new HttpParams();
    if (params) {
      Object.entries(params).forEach(([k, v]) => {
        httpParams = httpParams.set(k, v);
      });
    }
    return this.http
      .get<ApiCollection<T>>(`${this.baseUrl}${path}`, { params: httpParams })
      .pipe(map((res) => res.data), catchError(this.handleError));
  }

  // ── GET one → unwraps { data: {...} } ────────────────
  protected fetchOne<T>(path: string): Observable<T> {
    return this.http
      .get<ApiResource<T>>(`${this.baseUrl}${path}`)
      .pipe(map((res) => res.data), catchError(this.handleError));
  }

  // ── POST → create ─────────────────────────────────────
  protected create<T>(path: string, body: unknown): Observable<T> {
    return this.http
      .post<ApiResource<T>>(`${this.baseUrl}${path}`, body)
      .pipe(map((res) => res.data), catchError(this.handleError));
  }

  // ── PUT → update ──────────────────────────────────────
  protected update<T>(path: string, body: unknown): Observable<T> {
    return this.http
      .put<ApiResource<T>>(`${this.baseUrl}${path}`, body)
      .pipe(map((res) => res.data), catchError(this.handleError));
  }

  // ── DELETE → void (204) ───────────────────────────────
  protected remove(path: string): Observable<void> {
    return this.http
      .delete<void>(`${this.baseUrl}${path}`)
      .pipe(catchError(this.handleError));
  }

  // ── POST action (convert, send, etc.) ─────────────────
  protected action<T>(path: string, body: unknown = {}): Observable<T> {
    return this.http
      .post<T>(`${this.baseUrl}${path}`, body)
      .pipe(catchError(this.handleError));
  }

  // ── Error handler ─────────────────────────────────────
  private handleError(error: HttpErrorResponse): Observable<never> {
    let message = 'An unexpected error occurred.';

    if (error.status === 0)   message = 'Cannot connect to the server.';
    else if (error.status === 401) message = 'Session expired. Please log in again.';
    else if (error.status === 403) message = 'You do not have permission.';
    else if (error.status === 404) message = 'Resource not found.';
    else if (error.status === 422) return throwError(() => error); // pass full error for field validation
    else if (error.status >= 500)  message = 'Server error. Please try again later.';
    else if (error.error?.message) message = error.error.message;

    return throwError(() => ({ ...error, userMessage: message }));
  }
}