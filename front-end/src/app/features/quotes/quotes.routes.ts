import { Routes } from '@angular/router';

export const quotesRoutes: Routes = [
  {
    path: '',
    title: 'Quotes — Quvio',
    loadComponent: () =>
      import('./quote-list/quote-list.component').then(m => m.QuoteListComponent),
  },
  {
    path: 'new',
    title: 'New Quote — Quvio',
    loadComponent: () =>
      import('./quote-form/quote-form.component').then(m => m.QuoteFormComponent),
  },
  {
    path: ':id/edit',
    title: 'Edit Quote — Quvio',
    loadComponent: () =>
      import('./quote-form/quote-form.component').then(m => m.QuoteFormComponent),
  },
  {
    path: ':id/preview',
    title: 'Quote Preview — Quvio',
    loadComponent: () =>
      import('./quote-preview/quote-preview.component').then(m => m.QuotePreviewComponent),
  },
  {
    path: ':id',
    title: 'Quote Detail — Quvio',
    loadComponent: () =>
      import('./quote-detail/quote-detail.component').then(m => m.QuoteDetailComponent),
  },
];