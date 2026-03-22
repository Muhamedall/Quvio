// ============================================================
// dashboard.service.ts
// ============================================================
import { Injectable }    from '@angular/core';
import { Observable }    from 'rxjs';
import { ApiService }    from './api.service';
import { DashboardData } from '../models';

@Injectable({ providedIn: 'root' })
export class DashboardService extends ApiService {

  // GET /api/dashboard
  // Token added automatically by auth.interceptor.ts
  getStats(): Observable<DashboardData> {
    return this.http.get<DashboardData>(`${this.baseUrl}/dashboard`);
  }
}