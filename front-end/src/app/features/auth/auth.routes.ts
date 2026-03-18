// ============================================================
// auth.routes.ts
//
// Defines the URL routes for the entire auth feature.
//
// LAZY LOADING:
//   loadComponent() = Angular only downloads the component's
//   JavaScript bundle when the user first navigates to that URL.
//   This keeps the initial app bundle small and loads fast.
//
//   Without lazy loading: ALL components are bundled together
//   and the browser downloads everything on first visit.
//
//   With lazy loading: only the code needed for the current
//   page is downloaded — the rest comes later when needed.
//
// ROUTES DEFINED HERE:
//   /auth          → redirects to /auth/login
//   /auth/login    → LoginComponent
//   /auth/register → RegisterComponent
//
// HOW TO REGISTER IN app.routes.ts:
//   {
//     path: 'auth',
//     loadChildren: () => import('./features/auth/auth.routes').then(m => m.authRoutes)
//   }
// ============================================================

import { Routes } from '@angular/router';

export const authRoutes: Routes = [
  {
    path: '',
    redirectTo: 'login',
    pathMatch: 'full', // Only redirect when the path is exactly '' (not a sub-path)
  },
  {
    path: 'login',
    // loadComponent = lazy load this single component
    // The () => import(...) is a dynamic import — runs only when needed
    loadComponent: () =>
      import('./login/login').then((m) => m.LoginComponent),
  },
  {
    path: 'register',
    loadComponent: () =>
      import('./register/register').then((m) => m.RegisterComponent),
  },
];