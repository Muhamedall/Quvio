// ============================================================
// src/app/core/auth/index.ts  —  BARREL FILE
//
// Exports everything from the auth subfolder of core.
//
// USAGE:
//   import { AuthService }     from '@core/auth';
//   import { authGuard }       from '@core/auth';
//   import { authInterceptor } from '@core/auth';
//
// All three in one line:
//   import { AuthService, authGuard, authInterceptor } from '@core/auth';
// ============================================================

export { AuthService }     from './auth.service';
export { authGuard }       from './auth.guard';
export { authInterceptor } from './auth.interceptor';