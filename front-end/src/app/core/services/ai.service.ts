// ============================================================
// ai.service.ts
// Calls the Laravel AI endpoint.
// Add this to: src/app/core/services/ai.service.ts
// Also export it from src/app/core/services/index.ts
// ============================================================

import { Injectable, inject } from '@angular/core';
import { HttpClient }          from '@angular/common/http';
import { Observable }          from 'rxjs';
import { map }                 from 'rxjs/operators';
import { environment }         from '../../../environments/environment';

export interface AiGeneratedItem {
  description: string;
  quantity:    number;
  unit_price:  number;
  subtotal:    number;
}

export interface AiGenerateResponse {
  items:   AiGeneratedItem[];
  message: string;
}

@Injectable({ providedIn: 'root' })
export class AiService {

  private http    = inject(HttpClient);
  private baseUrl = environment.apiUrl;

  // POST /api/ai/generate-items
  generateItems(description: string, currency = 'EUR'): Observable<AiGeneratedItem[]> {
    return this.http
      .post<AiGenerateResponse>(`${this.baseUrl}/ai/generate-items`, {
        description,
        currency,
      })
      .pipe(map(r => r.items));
  }
}