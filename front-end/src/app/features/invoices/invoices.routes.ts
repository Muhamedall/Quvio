import { Routes } from '@angular/router';

export const invoicesRoutes: Routes = [
  {
    path: '',
    title: 'Invoices — Quvio',
    loadComponent: () =>
      import('./invoice-list/invoice-list.component').then(m => m.InvoiceListComponent),
  },
  {
    path: 'new',
    title: 'New Invoice — Quvio',
    loadComponent: () =>
      import('./invoice-form/invoice-form.component').then(m => m.InvoiceFormComponent),
  },
  {
    path: ':id/edit',
    title: 'Edit Invoice — Quvio',
    loadComponent: () =>
      import('./invoice-form/invoice-form.component').then(m => m.InvoiceFormComponent),
  },
  {
    path: ':id/preview',
    title: 'Invoice Preview — Quvio',
    loadComponent: () =>
      import('./invoice-preview/invoice-preview.component').then(m => m.InvoicePreviewComponent),
  },
  {
    path: ':id',
    title: 'Invoice Detail — Quvio',
    loadComponent: () =>
      import('./invoice-detail/invoice-detail.component').then(m => m.InvoiceDetailComponent),
  },
];