// ============================================================
// shared/components/index.ts  — BARREL FILE
//
// WHY A BARREL FILE?
// Instead of writing long relative imports in every feature component:
//   import { ButtonComponent }     from '../../../shared/components/button/button.component';
//   import { CardComponent }       from '../../../shared/components/card/card.component';
//   import { BadgeComponent }      from '../../../shared/components/badge/badge.component';
//
// You write ONE clean import:
//   import { ButtonComponent, CardComponent, BadgeComponent } from '@shared/components';
//
// (requires a path alias in tsconfig.json — see comment at bottom)
// ============================================================

export { ButtonComponent }     from './button/button';
export { BadgeComponent }      from './badge/badge';
export { CardComponent }       from './card/card';
export { TableComponent }      from './table/table';
export { LoaderComponent }     from './loader/loader';
export { EmptyStateComponent } from './empty-state/empty-state';
export { ModalComponent }      from './modal/modal';

// Also re-export types so consumers can use them without extra imports
export type { ButtonVariant, ButtonSize }  from './button/button';
export type { BadgeVariant, BadgeStatus }  from './badge/badge';
export type { ModalSize }                  from './modal/modal';
export type { TableColumn }                from './table/table';
export type { EmptyIcon }                  from './empty-state/empty-state';

// ── TSCONFIG PATH ALIAS SETUP ────────────────────────────
// To use @shared/components imports, add this to tsconfig.json:
//
// "compilerOptions": {
//   "paths": {
//     "@shared/*": ["src/app/shared/*"]
//   }
// }