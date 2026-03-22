// ── Auth types (user.model.ts) ───────────────────────────
export type {
  User,
  LoginPayload,
  RegisterPayload,
  AuthResponse,
} from './user.model';
 
// ── API types (api.models.ts) ────────────────────────────
export type {
  // Client
  Client,
  CreateClientPayload,
  UpdateClientPayload,
 
  // Items
  InvoiceItem,
  InvoiceItemPayload,
 
  // Quote
  Quote,
  QuoteStatus,
  CreateQuotePayload,
  UpdateQuotePayload,
 
  // Invoice
  Invoice,
  InvoiceStatus,
  CreateInvoicePayload,
  UpdateInvoicePayload,
 
  // Dashboard
  DashboardData,
  DashboardRevenue,
  DashboardInvoiceStats,
  DashboardQuoteStats,
  MonthlyRevenue,
  RecentInvoice,
  RecentQuote,
 
  // API wrappers
  ApiResource,
  ApiCollection,
} from './api.models';
 