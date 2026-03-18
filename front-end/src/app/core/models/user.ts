// ============================================================
// user.model.ts
//
// Defines the TypeScript "shape" of all auth-related objects.
//
// WHY INTERFACES?
// TypeScript uses these to catch mistakes at build time.
// If you try to access user.phone but phone isn't in the
// interface → TypeScript gives you an error immediately,
// before the code ever runs.
//
// WHERE THESE ARE USED:
//   User          → stored in localStorage + shown in navbar
//   LoginPayload  → sent to POST /api/auth/login
//   RegisterPayload → sent to POST /api/auth/register
//   AuthResponse  → what Laravel sends back after login/register
// ============================================================

// The logged-in user object
export interface User {
  id: number;
  name: string;
  email: string;
  created_at?: string; // optional — comes from Laravel timestamps
}

// What we POST to /api/auth/login
export interface LoginPayload {
  email: string;
  password: string;
}

// What we POST to /api/auth/register
export interface RegisterPayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string; // Laravel requires this field name exactly
}

// What Laravel sends BACK after a successful login or register
export interface AuthResponse {
  user: User;
  token: string; // Laravel Sanctum personal access token
}