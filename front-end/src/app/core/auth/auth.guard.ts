// ============================================================
// auth.guard.ts
//
// A ROUTE GUARD — runs before Angular activates a protected route.
//
// HOW IT WORKS:
//   Angular checks this guard before loading any protected page.
//   ✅ Token exists → return true  → page loads normally
//   ❌ No token    → redirect to /auth/login → page blocked
//
// WHERE IT'S USED:
//   In app.routes.ts, on every route that requires login:
//   { path: 'dashboard', canActivate: [authGuard], ... }
//   { path: 'invoices',  canActivate: [authGuard], ... }
//
// CanActivateFn is the modern Angular 17+ functional style.
// No class needed — just a function that returns true or a redirect.
// ============================================================

import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from './auth.service';

export const authGuard: CanActivateFn = () => {
  const auth   = inject(AuthService);
  const router = inject(Router);

  if (auth.isAuthenticated()) {
    return true; // ✅ Logged in — allow access to the route
  }

  // ❌ Not logged in — redirect to login page
  // createUrlTree creates a redirect instruction (not a component render)
  return router.createUrlTree(['/auth/login']);
};