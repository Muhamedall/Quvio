import { Injectable } from '@angular/core';
import { Observable }  from 'rxjs';
import { map }         from 'rxjs/operators';
import { ApiService }  from './api.service';
import { Quote }       from '../models';

@Injectable({ providedIn: 'root' })
export class QuoteService extends ApiService {

  private base = `${this.baseUrl}/quotes`;

  // GET /api/quotes
  getAll(): Observable<Quote[]> {
    return this.http.get<{ data: Quote[] }>(this.base)
      .pipe(map(r => r.data));
  }

  // GET /api/quotes/{uuid}
  getById(uuid: string): Observable<Quote> {
    return this.http.get<{ data: Quote }>(`${this.base}/${uuid}`)
      .pipe(map(r => r.data));
  }

  // POST /api/quotes
  store(payload: any): Observable<Quote> {
    return this.http.post<{ data: Quote }>(this.base, payload)
      .pipe(map(r => r.data));
  }

  // PUT /api/quotes/{uuid}
  patch(uuid: string, payload: any): Observable<Quote> {
    return this.http.put<{ data: Quote }>(`${this.base}/${uuid}`, payload)
      .pipe(map(r => r.data));
  }

  // DELETE /api/quotes/{uuid}
  destroy(uuid: string): Observable<void> {
    return this.http.delete<void>(`${this.base}/${uuid}`);
  }

  // POST /api/quotes/{uuid}/send
  send(uuid: string): Observable<any> {
    return this.http.post<any>(`${this.base}/${uuid}/send`, {});
  }

  // POST /api/quotes/{uuid}/convert
  convert(uuid: string): Observable<{ message: string; invoice: { id: number; uuid: string } }> {
    return this.http.post<any>(`${this.base}/${uuid}/convert`, {});
  }

  // GET /api/quotes/{uuid}/pdf
  downloadPdf(uuid: string): Observable<Blob> {
    return this.http.get(`${this.base}/${uuid}/pdf`, { responseType: 'blob' });
  }
}