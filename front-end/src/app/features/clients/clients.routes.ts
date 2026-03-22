import { Routes } from '@angular/router';

export const clientsRoutes: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./client-list/client-list').then(m => m.ClientListComponent),
  },
];