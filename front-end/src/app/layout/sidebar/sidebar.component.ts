// ============================================================
// sidebar.component.ts
//
// The left navigation panel — visible on desktop, slides in
// on mobile when the hamburger button is clicked.
//
// INPUTS:
//   [isOpen]  → controls mobile open/close state
// OUTPUTS:
//   (closed)  → emits when user clicks the overlay on mobile
// ============================================================

import {
  Component, Input, Output, EventEmitter,
  inject, ChangeDetectionStrategy,
} from '@angular/core';
import { CommonModule }                      from '@angular/common';
import { RouterLink, RouterLinkActive }      from '@angular/router';
import { AuthService }                       from '../../core/auth';

interface NavItem {
  label: string;
  path:  string;
  icon:  string;
}

@Component({
  selector:        'app-sidebar',
  standalone:      true,
  imports:         [CommonModule, RouterLink, RouterLinkActive],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl:     './sidebar.component.html',
})
export class SidebarComponent {

  @Input()  isOpen = false;
  @Output() closed = new EventEmitter<void>();

  authService = inject(AuthService);

  readonly navItems: NavItem[] = [
    {
      label: 'Dashboard',
      path:  '/dashboard',
      icon:  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
      label: 'Invoices',
      path:  '/invoices',
      icon:  'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
      label: 'Quotes',
      path:  '/quotes',
      icon:  'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2',
    },
    {
      label: 'Clients',
      path:  '/clients',
      icon:  'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    },
    {
      label: 'Settings',
      path:  '/settings',
      icon:  'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    },
  ];

  logout(): void {
    this.authService.logout();
  }

  onLinkClick(): void {
    this.closed.emit(); // close sidebar on mobile after navigation
  }
}