// ============================================================
// footer.component.ts
// Simple footer shown at the bottom of the main content area.
// ============================================================

import { Component, ChangeDetectionStrategy } from '@angular/core';

@Component({
  selector:        'app-footer',
  standalone:      true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  template: `
    <footer class="shrink-0 border-t border-gray-200 bg-white px-6 py-3">
      <div class="flex items-center justify-between">
        <p class="text-xs text-gray-400">
          © {{ year }} Quvio. All rights reserved.
        </p>
      
      </div>
    </footer>
  `,
})
export class FooterComponent {
  readonly year = new Date().getFullYear();
}