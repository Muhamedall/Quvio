// ============================================================
// environment.ts  — DEVELOPMENT environment
//
// This file is loaded when you run: ng serve
//
// WHY THIS FILE EXISTS:
//   Different environments (dev, prod) need different values.
//   Example:
//     Dev  → API runs on http://localhost:8000
//     Prod → API runs on https://api.quvio.com
//
//   Instead of hardcoding URLs everywhere and changing them
//   before every deployment, we define them here ONCE.
//   The app reads from this file — you only change ONE place.
//
// HOW ANGULAR SWITCHES ENVIRONMENTS:
//   ng serve         → uses environment.ts       (this file)
//   ng build         → uses environment.ts       (this file)
//   ng build --prod  → uses environment.prod.ts  (swapped automatically)
//
//   Angular CLI reads angular.json "fileReplacements" config
//   and swaps environment.ts with environment.prod.ts at build time.
//   Your app code always imports from 'environment.ts' — Angular
//   handles the swap behind the scenes.
//
// HOW TO USE IN ANY SERVICE:
//   import { environment } from '@env/environment';
//   const url = environment.apiUrl; // → 'http://localhost:8000/api'
//
// ⚠️  NEVER put real secrets (passwords, private keys) here.
//    These files are committed to git and visible to everyone.
//    Use server-side environment variables for secrets.
// ============================================================

export const environment = {

  // production flag — false in dev, true in prod
  // Used in main.ts: if (!environment.production) { enableDebugTools }
  production: false,

  // Base URL of your Laravel API — NO trailing slash
  // Laravel runs on port 8000 by default with: php artisan serve
  apiUrl: 'http://localhost:8000/api',

  // App name — used in page titles, emails, etc.
  appName: 'Quvio',

  // Stripe publishable key — safe to expose (it's public)
  // Get this from https://dashboard.stripe.com/test/apikeys
  // This is a placeholder — replace with your real test key
  stripePublicKey: 'pk_test_51TGRnP0AYYzLoOAxkkyxiOzSghr7Sa2eZpaSmInF4JQddAIMbLAjE5KGrs7rNOncXRJ3u87HsALSXOirWzS1abhr00g7gSIVLU',

};