import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }                       from '@angular/common';
import { RouterLink }                         from '@angular/router';
import { ClientService }                      from '../../../core/services';
import { Client }                              from '../../../../core/models';
import { CardComponent, TableComponent, ButtonComponent, LoaderComponent, EmptyStateComponent, ModalComponent } from '../../../shared/components';
import { ClientFormComponent }                from '../client-form/client-form.component';

@Component({
  selector:    'app-client-list',
  standalone:  true,
  imports:     [CommonModule, RouterLink, CardComponent, TableComponent, ButtonComponent,
                LoaderComponent, EmptyStateComponent, ModalComponent, ClientFormComponent],
  templateUrl: './client-list.html',
})
export class ClientListComponent implements OnInit {

  private clientService = inject(ClientService);

  clients    = signal<Client[]>([]);
  loading    = signal(true);
  showModal  = signal(false);
  editClient = signal<Client | null>(null);

  readonly columns = [
    { label: 'Name',    key: 'name' },
    { label: 'Company', key: 'company' },
    { label: 'Email' },
    { label: 'Phone' },
    { label: 'Actions' },
  ];

  ngOnInit(): void { this.load(); }

  load(): void {
    this.loading.set(true);
    this.clientService.getAll().subscribe({
      next:  (data) => { this.clients.set(data); this.loading.set(false); },
      error: ()     => { this.loading.set(false); },
    });
  }

  openCreate(): void { this.editClient.set(null); this.showModal.set(true); }
  openEdit(c: Client): void { this.editClient.set(c); this.showModal.set(true); }
  closeModal(): void { this.showModal.set(false); this.editClient.set(null); }

  onSaved(): void { this.closeModal(); this.load(); }

  delete(client: Client): void {
    if (!confirm(`Delete ${client.display_name}?`)) return;
    this.clientService.destroy(client.id).subscribe(() => this.load());
  }
}