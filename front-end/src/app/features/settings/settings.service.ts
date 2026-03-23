// ============================================================
// settings.service.ts
// Handles all settings API calls.
// ============================================================

import { Injectable }    from '@angular/core';
import { Observable }    from 'rxjs';
import { ApiService }    from '../../core/services/api.service';

export interface BrandingSettings {
  company_name:    string;
  company_address: string;
  company_phone:   string;
  company_email:   string;
  company_website: string;
  invoice_notes:   string;
  invoice_prefix:  string;
  quote_prefix:    string;
}

export interface UpdateProfilePayload {
  name:  string;
  email: string;
}

export interface UpdatePasswordPayload {
  current_password:      string;
  password:              string;
  password_confirmation: string;
}

@Injectable({ providedIn: 'root' })
export class SettingsService extends ApiService {

  // GET /api/settings/profile
  getProfile(): Observable<{ user: any }> {
    return this.http.get<{ user: any }>(`${this.baseUrl}/settings/profile`);
  }

  // PUT /api/settings/profile
  updateProfile(payload: UpdateProfilePayload): Observable<{ message: string; user: any }> {
    return this.http.put<{ message: string; user: any }>(
      `${this.baseUrl}/settings/profile`, payload
    );
  }

  // PUT /api/settings/password
  updatePassword(payload: UpdatePasswordPayload): Observable<{ message: string }> {
    return this.http.put<{ message: string }>(
      `${this.baseUrl}/settings/password`, payload
    );
  }

  // GET /api/settings/branding
  getBranding(): Observable<{ branding: BrandingSettings }> {
    return this.http.get<{ branding: BrandingSettings }>(`${this.baseUrl}/settings/branding`);
  }

  // PUT /api/settings/branding
  updateBranding(payload: Partial<BrandingSettings>): Observable<{ message: string }> {
    return this.http.put<{ message: string }>(
      `${this.baseUrl}/settings/branding`, payload
    );
  }
}