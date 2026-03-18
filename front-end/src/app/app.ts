// ============================================================
// app.component.ts
//
// THIS REPLACES THE GENERATED:
//   export class App {
//     protected readonly title = signal('front-end');
//   }
//
// WHAT CHANGED AND WHY:
//   1. Class renamed App → AppComponent  (Angular convention)
//   2. selector: 'app-root'              (matches <app-root> in index.html)
//   3. templateUrl → inline template     (just one line — no need for a file)
//   4. Removed title signal              (not needed — layout handles titles)
//   5. Kept RouterOutlet import          (exactly as CLI generated)
//
// WHY IS THIS SO SIMPLE?
//   AppComponent is the ROOT mounting point — nothing more.
//   The router reads the URL and renders the right component
//   INSIDE <router-outlet>:
//     /auth/login  → LoginComponent     replaces <router-outlet>
//     /dashboard   → MainLayoutComponent replaces <router-outlet>
//                    (which itself has its own <router-outlet> for children)
// ============================================================

import { Component }    from '@angular/core';
import { RouterOutlet } from '@angular/router';

@Component({
  selector:   'app-root',          // must match <app-root> in index.html
  standalone: true,
  imports:    [RouterOutlet],
  template:   `<router-outlet />`, // router decides what renders here
})
export class App {}