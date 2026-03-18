// ============================================================
// modal.component.ts
//
// A reusable dialog/modal used for:
//   - Create/Edit forms (New Client, New Invoice)
//   - Delete confirmation dialogs
//   - Invoice/Quote preview
//
// HOW IT WORKS:
//   - Parent controls visibility via [isOpen] input
//   - Modal emits (closed) when user clicks backdrop or X button
//   - Content is projected via ng-content slots
//
// USAGE:
//   <app-modal
//     [isOpen]="showModal"
//     title="New Invoice"
//     size="lg"
//     (closed)="showModal = false"
//   >
//     <!-- Body content -->
//     <p>Form fields here...</p>
//
//     <!-- Footer buttons -->
//     <div slot="footer">
//       <app-button variant="secondary" (click)="showModal = false">Cancel</app-button>
//       <app-button (click)="save()">Save Invoice</app-button>
//     </div>
//   </app-modal>
// ============================================================

import {
  Component, Input, Output, EventEmitter,
  ChangeDetectionStrategy, HostListener,
} from '@angular/core';
import { CommonModule } from '@angular/common';

export type ModalSize = 'sm' | 'md' | 'lg' | 'xl';

@Component({
  selector: 'app-modal',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './modal.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ModalComponent {

  @Input() isOpen = false;
  @Input() title = '';
  @Input() subtitle = '';
  @Input() size: ModalSize = 'md';
  @Input() closable = true;           // Show X button and allow backdrop close

  @Output() closed = new EventEmitter<void>();

  // Size → max-width Tailwind class
  readonly sizeClasses: Record<ModalSize, string> = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
  };

  // Close when pressing Escape key
  // @HostListener listens to events on the component's host element or the document
  @HostListener('document:keydown.escape')
  onEscapeKey(): void {
    if (this.isOpen && this.closable) {
      this.close();
    }
  }

  close(): void {
    this.closed.emit();
  }

  // Close when clicking the backdrop (outside the dialog box)
  // We check that the click was on the backdrop element itself,
  // not a child element bubbling up
  onBackdropClick(event: MouseEvent): void {
    if (!this.closable) return;
    // event.target is the element clicked
    // event.currentTarget is the backdrop div
    // If they match, the click was ON the backdrop (not inside the modal)
    if (event.target === event.currentTarget) {
      this.close();
    }
  }
}