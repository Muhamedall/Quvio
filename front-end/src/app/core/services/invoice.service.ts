import { Injectable } from '@angular/core';
import { Observable }  from 'rxjs';
import { map }         from 'rxjs/operators';
import { ApiService }  from './api.service';
import { Invoice }     from '../models';

@Injectable({ providedIn: 'root' })
export class InvoiceService extends ApiService {

  private base = `${this.baseUrl}/invoices`;

  // GET /api/invoices
  getAll(): Observable<Invoice[]> {
    return this.http.get<{ data: Invoice[] }>(this.base)
      .pipe(map(r => r.data));
  }

  // GET /api/invoices/{uuid}
  getById(uuid: string): Observable<Invoice> {
    return this.http.get<{ data: Invoice }>(`${this.base}/${uuid}`)
      .pipe(map(r => r.data));
  }

  // POST /api/invoices
  store(payload: any): Observable<Invoice> {
    return this.http.post<{ data: Invoice }>(this.base, payload)
      .pipe(map(r => r.data));
  }

  // PUT /api/invoices/{uuid}
  patch(uuid: string, payload: any): Observable<Invoice> {
    return this.http.put<{ data: Invoice }>(`${this.base}/${uuid}`, payload)
      .pipe(map(r => r.data));
  }

  // DELETE /api/invoices/{uuid}
  destroy(uuid: string): Observable<void> {
    return this.http.delete<void>(`${this.base}/${uuid}`);
  }

  // POST /api/invoices/{uuid}/send
  send(uuid: string): Observable<any> {
    return this.http.post<any>(`${this.base}/${uuid}/send`, {});
  }

  // GET /api/invoices/{uuid}/pdf → returns Blob for download
  downloadPdf(uuid: string): Observable<Blob> {
    return this.http.get(`${this.base}/${uuid}/pdf`, { responseType: 'blob' });
  }

  // POST /api/invoices/{uuid}/payment-link
  generatePaymentLink(uuid: string): Observable<{ stripe_link: string }> {
    return this.http.post<{ stripe_link: string }>(
      `${this.base}/${uuid}/payment-link`, {}
    );
  }
}