// ============================================================
// quote.service.ts
// ============================================================
import { Injectable }                                                from '@angular/core';
import { Observable }                                                from 'rxjs';
import { ApiService }                                                from './api.service';
import { Quote, CreateQuotePayload, UpdateQuotePayload, Invoice }    from '../models';

@Injectable({ providedIn: 'root' })
export class QuoteService extends ApiService {

  private readonly path = '/quotes';

  // GET /api/quotes
  getAll(): Observable<Quote[]> {
    return this.fetchAll<Quote>(this.path);
  }

  // GET /api/quotes/:id
  getById(id: number): Observable<Quote> {
    return this.fetchOne<Quote>(`${this.path}/${id}`);
  }

  // POST /api/quotes
  store(payload: CreateQuotePayload): Observable<Quote> {
    return this.create<Quote>(this.path, payload);
  }

  // PUT /api/quotes/:id
  patch(id: number, payload: UpdateQuotePayload): Observable<Quote> {
    return this.update<Quote>(`${this.path}/${id}`, payload);
  }

  // DELETE /api/quotes/:id
  destroy(id: number): Observable<void> {
    return this.remove(`${this.path}/${id}`);
  }

  // POST /api/quotes/:id/send
  send(id: number): Observable<{ message: string; quote: Quote }> {
    return this.action(`${this.path}/${id}/send`);
  }

  // POST /api/quotes/:id/convert
  convert(id: number): Observable<{ message: string; invoice: Invoice }> {
    return this.action(`${this.path}/${id}/convert`);
  }
}