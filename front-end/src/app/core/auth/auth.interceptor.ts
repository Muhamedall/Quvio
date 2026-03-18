// ============================================================
// auth.interceptor.ts
//
// An HTTP INTERCEPTOR — automatically runs on EVERY outgoing request.
//
// WHY IT EXISTS:
//   Without this, you'd have to manually add the token header
//   in every single service method:
//     this.http.get('/api/invoices', { headers: { Authorization: `Bearer ${token}` } })
//
//   With the interceptor, you just write:
//     this.http.get('/api/invoices')
//   ...and the token is added automatically. Much cleaner.
//
// WHAT IT DOES:
//   1. Reads the token from AuthService
//   2. Clones the original request (requests are immutable)
//   3. Adds the Authorization header to the clone
//   4. Sends the modified request forward
//
// HttpInterceptorFn is the modern Angular 17+ functional style.
// Register it in app.config.ts using provideHttpClient(withInterceptors([authInterceptor]))
// ============================================================

import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { AuthService } from './auth.service';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const token = inject(AuthService).getToken();

  // If no token (e.g. login/register requests), send as-is
  if (!token) return next(req);

  // Clone the request and attach the Authorization header
  // We clone because HttpRequest objects are immutable
  const authReq = req.clone({
    setHeaders: {
      Authorization: `Bearer ${token}`,
      Accept:        'application/json',  // Tell Laravel to return JSON, not HTML
    },
  });

  return next(authReq); // Pass the modified request down the chain
};