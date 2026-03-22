import { Routes }    from '@angular/router';
import { authGuard } from './core/auth/auth.guard';

export const routes: Routes = [

  // ── DEFAULT → redirect to login ──────────────────────
  {
    path:       '',
    redirectTo: 'auth/login',
    pathMatch:  'full',
  },

  // ════════════════════════════════════════════════════
  // PUBLIC — no auth, no layout
  // ════════════════════════════════════════════════════
  {
    path: 'auth',
    loadChildren: () =>
      import('./features/auth/auth.routes').then((m) => m.authRoutes),
  },

  // ════════════════════════════════════════════════════
  // PROTECTED — auth guard + sidebar/navbar layout
  // ════════════════════════════════════════════════════
  {
    path:          '',
    canActivate:   [authGuard],
    loadComponent: () =>
      import('./layout/main-layout.component')
        .then((m) => m.MainLayoutComponent),

    children: [

      // /dashboard
      {
        path:  'dashboard',
        title: 'Dashboard — Quvio',
        loadComponent: () =>
          import('./features/dashboard/dashboard/dashboard')
            .then((m) => m.DashboardComponent),
      },

      // /clients
      {
        path:  'clients',
        title: 'Clients — Quvio',
        loadChildren: () =>
          import('./features/clients/clients.routes')
            .then((m) => m.clientsRoutes),
      },

      // /quotes
      {
        path:  'quotes',
        title: 'Quotes — Quvio',
        loadChildren: () =>
          import('./features/quotes/quotes.routes')
            .then((m) => m.quotesRoutes),
      },

      // /invoices
      {
        path:  'invoices',
        title: 'Invoices — Quvio',
        loadChildren: () =>
          import('./features/invoices/invoices.routes')
            .then((m) => m.invoicesRoutes),
      },

      // /settings
      {
        path:  'settings',
        title: 'Settings — Quvio',
        loadChildren: () =>
          import('./features/settings/settings.routes')
            .then((m) => m.settingsRoutes),
      },

      // Unknown protected URL → dashboard
      {
        path:       '**',
        redirectTo: 'dashboard',
      },
    ],
  },

  // Public 404 → login
  {
    path:       '**',
    redirectTo: 'auth/login',
  },

];