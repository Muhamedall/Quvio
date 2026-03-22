// ============================================================
// invoice.service.ts
// ============================================================
import { Injectable }                                                        from '@angular/core';
import { Observable }                                                        from 'rxjs';
import { ApiService }                                                        from './api.service';
import { Invoice, InvoiceStatus, CreateInvoicePayload, UpdateInvoicePayload } from '../models';

@Injectable({ providedIn: 'root' })
export class InvoiceService extends ApiService {

  private readonly path = '/invoices';

  // GET /api/invoices  (optional ?status=unpaid filter)
  getAll(status?: InvoiceStatus): Observable<Invoice[]> {
    const params = status ? { status } : undefined;
    return this.fetchAll<Invoice>(this.path, params);
  }

  // GET /api/invoices/:id
  getById(id: number): Observable<Invoice> {
    return this.fetchOne<Invoice>(`${this.path}/${id}`);
  }

  // POST /api/invoices
  store(payload: CreateInvoicePayload): Observable<Invoice> {
    return this.create<Invoice>(this.path, payload);
  }

  // PUT /api/invoices/:id
  patch(id: number, payload: UpdateInvoicePayload): Observable<Invoice> {
    return this.update<Invoice>(`${this.path}/${id}`, payload);
  }

  // DELETE /api/invoices/:id
  destroy(id: number): Observable<void> {
    return this.remove(`${this.path}/${id}`);
  }

  // POST /api/invoices/:id/send
  send(id: number): Observable<{ message: string }> {
    return this.action(`${this.path}/${id}/send`);
  }

  // POST /api/invoices/:id/payment-link
  generatePaymentLink(id: number): Observable<{ stripe_link: string; message: string }> {
    return this.action(`${this.path}/${id}/payment-link`);
  }

  // GET /api/invoices/:id/pdf  (returns binary Blob)
  downloadPdf(id: number): Observable<Blob> {
    return this.http.get(
      `${this.baseUrl}${this.path}/${id}/pdf`,
      { responseType: 'blob' }
    );
  }
}