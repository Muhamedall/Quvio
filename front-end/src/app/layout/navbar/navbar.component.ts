// ============================================================
// navbar.component.ts
//
// Top navigation bar — shows inside the main layout.
// Contains: hamburger menu (mobile), page title, quick actions.
//
// OUTPUTS:
//   (menuToggled) → emits when hamburger is clicked
//                   MainLayout listens and toggles the sidebar
// ============================================================

import {
  Component, Output, EventEmitter, inject, ChangeDetectionStrategy,
} from '@angular/core';
import { CommonModule }  from '@angular/common';
import { RouterLink }    from '@angular/router';
import { AuthService }   from '../../core/auth';

@Component({
  selector:        'app-navbar',
  standalone:      true,
  imports:         [CommonModule, RouterLink],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl:     './navbar.component.html',
})
export class NavbarComponent {

  @Output() menuToggled = new EventEmitter<void>();

  authService = inject(AuthService);
}