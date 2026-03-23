import { Routes } from '@angular/router';

export const settingsRoutes: Routes = [
  {
    path: '',
    redirectTo: 'profile',
    pathMatch: 'full',
  },
  {
    path: 'profile',
    title: 'Profile — Quvio',
    loadComponent: () =>
      import('./profile/profile.component').then(m => m.ProfileComponent),
  },
  {
    path: 'branding',
    title: 'Branding — Quvio',
    loadComponent: () =>
      import('./branding/branding.component').then(m => m.BrandingComponent),
  },
];