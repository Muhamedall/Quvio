// ============================================================
// app.routes.ts
//
// THIS REPLACES THE EMPTY:
//   export const routes: Routes = [];
//
// DEFAULT PAGE = LOGIN
//   / → redirects to /auth/login
//   Angular opens the login page first before anything else.
//
// TWO ROUTE TREES:
//
//   PUBLIC  — no guard, no layout
//   /auth/login    → LoginComponent
//   /auth/register → RegisterComponent
//
//   PROTECTED — guarded + wrapped in sidebar/navbar layout
//   /dashboard → DashboardComponent   (inside MainLayoutComponent)
//   /invoices  → InvoicesRoutes       (inside MainLayoutComponent)
//   /quotes    → QuotesRoutes         (inside MainLayoutComponent)
//   /clients   → ClientsRoutes        (inside MainLayoutComponent)
//   /settings  → SettingsRoutes       (inside MainLayoutComponent)
//
// HOW THE GUARD WORKS:
//   authGuard is applied ONCE on the parent layout route.
//   ALL children inherit it automatically — no repetition.
//   Not logged in → redirected to /auth/login.
// ============================================================

import { Routes }    from '@angular/router';
import { authGuard } from './core/auth/auth.guard';

export const routes: Routes = [

  // ── DEFAULT: redirect root to login ──────────────────
  // This makes /auth/login the first page a visitor sees
  {
    path:       '',
    redirectTo: 'auth/login',
    pathMatch:  'full',
  },

  // ════════════════════════════════════════════════════
  // PUBLIC — no auth, no layout shell
  // ════════════════════════════════════════════════════
  {
    path: 'auth',
    loadChildren: () =>
      import('./features/auth/auth.routes').then((m) => m.authRoutes),
  },

  // ════════════════════════════════════════════════════
  // PROTECTED — guarded + wrapped in MainLayoutComponent
  //
  // WHY path: '' here too?
  //   These routes live at the root level (/dashboard, /invoices...)
  //   but we still need a parent route to attach:
  //     - canActivate: [authGuard]  → protect all children at once
  //     - loadComponent: MainLayout → sidebar + navbar shell
  //   Using path: '' means the layout adds no URL segment of its own.
  // ════════════════════════════════════════════════════
  /*
  {
    path:          '',
    canActivate:   [authGuard],
    loadComponent: () =>
      import('./layout/main-layout.component')
        .then((m) => m.MainLayoutComponent),

    // All protected pages render inside MainLayout's <router-outlet>
    children: [
      {
        path:          'dashboard',
        title:         'Dashboard — Quvio',
        loadComponent: () =>
          import('./features/dashboard/dashboard.component')
            .then((m) => m.DashboardComponent),
      },
      {
        path: 'invoices',
        loadChildren: () =>
          import('./features/invoices/invoices.routes')
            .then((m) => m.invoicesRoutes),
      },
      {
        path: 'quotes',
        loadChildren: () =>
          import('./features/quotes/quotes.routes')
            .then((m) => m.quotesRoutes),
      },
      {
        path: 'clients',
        loadChildren: () =>
          import('./features/clients/clients.routes')
            .then((m) => m.clientsRoutes),
      },
      {
        path: 'settings',
        loadChildren: () =>
          import('./features/settings/settings.routes')
            .then((m) => m.settingsRoutes),
      },
      // Any unknown protected URL → go to dashboard
      {
        path:       '**',
        redirectTo: 'dashboard',
      },
    ],
  },
*/
  // ── Public 404 → login ───────────────────────────────
  {
    path:       '**',
    redirectTo: 'auth/login',
  },

];