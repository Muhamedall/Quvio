// ============================================================
// table.component.ts
//
// Provides the shell for all data tables in Quvio.
// Parent defines what goes in each <tr> — this handles:
//   - Column headers with optional sorting
//   - Loading skeleton rows
//   - Empty state when no data
//
// USAGE:
//   <app-table
//     [columns]="[{ label:'Client' }, { label:'Amount', key:'total', align:'right' }]"
//     [loading]="isLoading"
//     [empty]="list.length === 0"
//     emptyTitle="No invoices yet"
//   >
//     @for (item of list; track item.id) {
//       <tr class="hover:bg-gray-50 border-b border-gray-100 last:border-0">
//         <td class="px-4 py-3 text-sm">{{ item.client }}</td>
//         <td class="px-4 py-3 text-sm text-right font-mono">{{ item.total }}</td>
//       </tr>
//     }
//   </app-table>
// ============================================================

import {
  Component, Input, Output, EventEmitter, ChangeDetectionStrategy
} from '@angular/core';
import { CommonModule } from '@angular/common';

export interface TableColumn {
  label: string;
  key?: string;                          // If set, column is sortable
  align?: 'left' | 'right' | 'center';
  width?: string;
}

@Component({
  selector: 'app-table',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './table.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TableComponent {

  @Input() columns: TableColumn[] = [];
  @Input() loading = false;
  @Input() skeletonRows = 5;
  @Input() empty = false;
  @Input() emptyTitle = 'No results found';
  @Input() emptySubtitle = 'Try adjusting your filters or create a new item.';
  @Input() sortKey = '';
  @Input() sortDir: 'asc' | 'desc' = 'asc';

  @Output() sortChange = new EventEmitter<{ key: string; dir: 'asc' | 'desc' }>();

  get skeletonArray(): number[] {
    return Array(this.skeletonRows).fill(0);
  }

  onSort(col: TableColumn): void {
    if (!col.key) return;
    const newDir = this.sortKey === col.key && this.sortDir === 'asc' ? 'desc' : 'asc';
    this.sortChange.emit({ key: col.key, dir: newDir });
  }

  isSorted(col: TableColumn): boolean {
    return !!col.key && col.key === this.sortKey;
  }

  getAlignClass(align?: string): string {
    if (align === 'right')  return 'text-right';
    if (align === 'center') return 'text-center';
    return 'text-left';
  }
}