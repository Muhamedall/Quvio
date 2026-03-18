// ============================================================
// button.component.ts
//
// A single reusable button that covers every variant in Quvio.
// The TypeScript builds the Tailwind class string dynamically
// based on the inputs — so parent templates stay clean.
//
// USAGE:
//   <app-button>Save</app-button>
//   <app-button variant="secondary" size="sm">Cancel</app-button>
//   <app-button variant="danger" [loading]="isDeleting">Delete</app-button>
//   <app-button type="submit" [fullWidth]="true">Sign in</app-button>
// ============================================================

import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';

export type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger' | 'danger-outline';
export type ButtonSize    = 'sm' | 'md' | 'lg';
export type ButtonType    = 'button' | 'submit' | 'reset';

@Component({
  selector: 'app-button',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './button.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ButtonComponent {

  @Input() variant: ButtonVariant = 'primary';
  @Input() size: ButtonSize = 'md';
  @Input() type: ButtonType = 'button';
  @Input() loading = false;
  @Input() disabled = false;
  @Input() fullWidth = false;
  @Input() iconPath = '';           // SVG path data for an optional icon
  @Input() iconPosition: 'left' | 'right' = 'left';

  get isDisabled(): boolean {
    return this.disabled || this.loading;
  }

  // ── TAILWIND CLASS MAPS ───────────────────────────────
  // Each variant and size maps to a fixed set of Tailwind classes.
  // We build these as computed strings so the template stays clean.

  private readonly variantClasses: Record<ButtonVariant, string> = {
    'primary':
      'bg-indigo-500 text-white hover:bg-indigo-600 active:bg-indigo-700 shadow-sm hover:shadow-md',
    'secondary':
      'bg-white text-gray-800 border border-gray-200 hover:bg-gray-50 hover:border-gray-300',
    'ghost':
      'bg-transparent text-indigo-500 hover:bg-indigo-50',
    'danger':
      'bg-red-500 text-white hover:bg-red-600 active:bg-red-700',
    'danger-outline':
      'bg-transparent text-red-500 border border-red-400 hover:bg-red-50',
  };

  private readonly sizeClasses: Record<ButtonSize, string> = {
    'sm':  'px-3 py-1.5 text-xs gap-1',
    'md':  'px-5 py-2.5 text-sm gap-2',
    'lg':  'px-7 py-3.5 text-base gap-2',
  };

  get buttonClasses(): string {
    return [
      // Base — shared by all buttons
      'inline-flex items-center justify-center font-semibold rounded-lg',
      'transition-all duration-200 focus-visible:outline-none',
      'focus-visible:ring-2 focus-visible:ring-indigo-400 focus-visible:ring-offset-2',
      'disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none',
      // Variant-specific
      this.variantClasses[this.variant],
      // Size-specific
      this.sizeClasses[this.size],
      // Optional full width
      this.fullWidth ? 'w-full' : '',
      // Loading cursor
      this.loading ? 'cursor-wait' : '',
    ].filter(Boolean).join(' ');
  }
}