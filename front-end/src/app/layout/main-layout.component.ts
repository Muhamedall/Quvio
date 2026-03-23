// ============================================================
// main-layout.component.ts
//
// The SHELL of the entire protected area.
// Composes: SidebarComponent + NavbarComponent + FooterComponent
// + <router-outlet> for the page content.
//
// This component owns the sidebar open/close state.
// It passes it DOWN to SidebarComponent via @Input()
// and receives toggle events UP from NavbarComponent via @Output()
// ============================================================

import { Component, signal } from '@angular/core';
import { RouterOutlet }       from '@angular/router';
import { CommonModule }       from '@angular/common';
import { SidebarComponent }   from './sidebar/sidebar.component';
import { NavbarComponent }    from './navbar/navbar.component';
import { FooterComponent }    from './footer/footer.component';

@Component({
  selector:    'app-main-layout',
  standalone:  true,
  imports:     [CommonModule, RouterOutlet, SidebarComponent, NavbarComponent, FooterComponent],
  templateUrl: './main-layout.component.html',
})
export class MainLayoutComponent {

  // Sidebar open/close state — owned here, shared down to children
  sidebarOpen = signal(false);

  toggle(): void { this.sidebarOpen.update(v => !v); }
  close():  void { this.sidebarOpen.set(false); }
}