// ============================================================
// environment.prod.ts  — PRODUCTION environment
//
// This file is loaded when you run: ng build --configuration=production
// Angular CLI automatically REPLACES environment.ts with this file.
//
// Your app code always imports from 'environment.ts' — you never
// import directly from environment.prod.ts. Angular does the swap.
//
// BEFORE DEPLOYING — update these values:
//   apiUrl       → your real Laravel server URL
//   stripePublicKey → your real Stripe LIVE key (pk_live_...)
// ============================================================

export const environment = {

  production: true,

  // Your deployed Laravel API URL — replace with your real domain
  apiUrl: 'https://api.quvio.com/api',

  appName: 'Quvio',

  // Stripe LIVE publishable key — replace before going live
  // Get from: https://dashboard.stripe.com/apikeys
  stripePublicKey: 'pk_live_REPLACE_WITH_YOUR_LIVE_KEY',

};