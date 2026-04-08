// ============================================================
// api.models.ts
//
// All TypeScript interfaces matching Laravel API responses.
// Auth types (User, AuthResponse, etc.) live in user.model.ts
// Everything else lives here.
// ============================================================

// ── CLIENT ───────────────────────────────────────────────

export interface Client {
  id:              number;
  name:            string;
  email:           string;
  phone:           string | null;
  company:         string | null;
  address:         string | null;
  display_name:    string;
  initials:        string;
  quotes_count?:   number;
  invoices_count?: number;
  created_at:      string;
  updated_at:      string;
}

export interface CreateClientPayload {
  name:     string;
  email:    string;
  phone?:   string;
  company?: string;
  address?: string;
}

export type UpdateClientPayload = Partial<CreateClientPayload>;

// ── INVOICE ITEM ─────────────────────────────────────────

export interface InvoiceItem {
  id:          number;
  description: string;
  quantity:    number;
  unit_price:  number;
  subtotal:    number;
}

export interface InvoiceItemPayload {
  description: string;
  quantity:    number;
  unit_price:  number;
}

// ── QUOTE ────────────────────────────────────────────────

export type QuoteStatus = 'draft' | 'sent' | 'approved' | 'rejected';

export interface Quote {
  id:           number;
  uuid:         string; 
  quote_number: string;
  status:       QuoteStatus;
  status_label: string;
  subtotal:     number;
  tax_rate:     number;
  total:        number;
  notes:        string | null;
  valid_until:  string | null;
  is_converted: boolean;
  client?:      Client;
  items?:       InvoiceItem[];
  created_at:   string;
  updated_at:   string;
}

export interface CreateQuotePayload {
  client_id:    number;
  tax_rate?:    number;
  notes?:       string;
  valid_until?: string;
  items:        InvoiceItemPayload[];
}

export type UpdateQuotePayload = Partial<CreateQuotePayload>;

// ── INVOICE ──────────────────────────────────────────────

export type InvoiceStatus = 'unpaid' | 'paid' | 'overdue';

export interface Invoice {
  id:             number;
  uuid:           string;
  invoice_number: string;
  status:         InvoiceStatus;
  status_label:   string;
  subtotal:       number;
  tax_rate:       number;
  total:          number;
  due_date:       string;
  paid_at:        string | null;
  days_until_due: number;
  stripe_link:    string | null;
  notes:          string | null;
  client?:        Client;
  quote?:         Quote;
  items?:         InvoiceItem[];
  created_at:     string;
  updated_at:     string;
}

export interface CreateInvoicePayload {
  client_id:  number;
  quote_id?:  number;
  tax_rate?:  number;
  due_date:   string;
  notes?:     string;
  items:      InvoiceItemPayload[];
}

export type UpdateInvoicePayload = Partial<CreateInvoicePayload>;

// ── DASHBOARD ────────────────────────────────────────────

export interface DashboardRevenue {
  total:      number;
  this_month: number;
  last_month: number;
  growth:     number;
}

export interface DashboardInvoiceStats {
  total:       number;
  unpaid:      number;
  paid:        number;
  overdue:     number;
  outstanding: number;
}

export interface DashboardQuoteStats {
  total:    number;
  draft:    number;
  sent:     number;
  approved: number;
  rejected: number;
}

export interface MonthlyRevenue {
  month:   string;
  revenue: number;
}

export interface RecentInvoice {
  id:             number;
  invoice_number: string;
  client_name:    string;
  status:         InvoiceStatus;
  status_label:   string;
  total:          number;
  due_date:       string;
}

export interface RecentQuote {
  id:           number;
  quote_number: string;
  client_name:  string;
  status:       QuoteStatus;
  status_label: string;
  total:        number;
  created_at:   string;
}

export interface DashboardData {
  revenue:         DashboardRevenue;
  invoices:        DashboardInvoiceStats;
  quotes:          DashboardQuoteStats;
  clients_count:   number;
  recent_invoices: RecentInvoice[];
  recent_quotes:   RecentQuote[];
  monthly_revenue: MonthlyRevenue[];
}

// ── API RESPONSE WRAPPERS ────────────────────────────────
// Laravel wraps resources in { "data": ... }

export interface ApiResource<T> {
  data: T;
}

export interface ApiCollection<T> {
  data: T[];
}