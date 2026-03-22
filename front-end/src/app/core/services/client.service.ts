// ============================================================
// client.service.ts
// USAGE:
//   private clientService = inject(ClientService);
//   this.clientService.getAll().subscribe(clients => ...)
// ============================================================
import { Injectable }                                          from '@angular/core';
import { Observable }                                          from 'rxjs';
import { ApiService }                                          from './api.service';
import { Client, CreateClientPayload, UpdateClientPayload }    from '../models';

@Injectable({ providedIn: 'root' })
export class ClientService extends ApiService {

  private readonly path = '/clients';

  // GET /api/clients
  getAll(): Observable<Client[]> {
    return this.fetchAll<Client>(this.path);
  }

  // GET /api/clients/:id
  getById(id: number): Observable<Client> {
    return this.fetchOne<Client>(`${this.path}/${id}`);
  }

  // POST /api/clients
  store(payload: CreateClientPayload): Observable<Client> {
    return this.create<Client>(this.path, payload);
  }

  // PUT /api/clients/:id
  patch(id: number, payload: UpdateClientPayload): Observable<Client> {
    return this.update<Client>(`${this.path}/${id}`, payload);
  }

  // DELETE /api/clients/:id
  destroy(id: number): Observable<void> {
    return this.remove(`${this.path}/${id}`);
  }
}