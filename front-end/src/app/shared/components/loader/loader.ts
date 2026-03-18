// ============================================================
// loader.component.ts
//
// Two modes:
//   inline  → centered block, used when a whole section is loading
//   overlay → absolute overlay on the parent container (add relative to parent)
//
// USAGE:
//   <app-loader />
//   <app-loader size="sm" color="white" />
//   <app-loader [overlay]="true" text="Saving..." />
// ============================================================

import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-loader',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './loader.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LoaderComponent {

  @Input() size: 'sm' | 'md' | 'lg' = 'md';
  @Input() overlay = false;
  @Input() text = '';
  @Input() color: 'primary' | 'white' | 'muted' = 'primary';

  get wrapperClasses(): string {
    if (this.overlay) {
      return 'absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-white/80 backdrop-blur-sm rounded-xl';
    }
    return 'flex flex-col items-center justify-center gap-3 py-16 w-full';
  }

  get spinnerClasses(): string {
    const sizes = { sm: 'w-5 h-5', md: 'w-8 h-8', lg: 'w-12 h-12' };
    const colors = {
      primary: 'text-indigo-500',
      white:   'text-white',
      muted:   'text-gray-400',
    };
    return `animate-spin ${sizes[this.size]} ${colors[this.color]}`;
  }

  get textClasses(): string {
    const sizes = { sm: 'text-xs', md: 'text-sm', lg: 'text-base' };
    return `${sizes[this.size]} text-gray-500 font-medium animate-pulse`;
  }
}