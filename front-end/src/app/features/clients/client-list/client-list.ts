import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule }    from '@angular/common';
import { RouterLink }      from '@angular/router';
import { ClientService }   from '../../../core/services';
import { Client }          from '../../../core/models';
import {
  ButtonComponent, BadgeComponent, TableComponent,
  LoaderComponent, EmptyStateComponent, ModalComponent,
} from '../../../shared/components';
import { ClientFormComponent } from '../client-form/client-form';

@Component({
  selector:    'app-client-list',
  standalone:  true,
  imports:     [CommonModule, RouterLink, ButtonComponent, BadgeComponent,
                TableComponent, LoaderComponent, EmptyStateComponent,
                ModalComponent, ClientFormComponent],
  templateUrl: './client-list.html',
})
export class ClientListComponent implements OnInit {

  private clientService = inject(ClientService);

  clients    = signal<Client[]>([]);
  loading    = signal(true);
  showModal  = signal(false);
  editClient = signal<Client | null>(null);

  readonly columns = [
    { label: 'Name' },
    { label: 'Company' },
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

  openCreate(): void {
    this.editClient.set(null);  // null = create mode, no pre-filled data
    this.showModal.set(true);
  }

  openEdit(client: Client, event: Event): void {
    event.stopPropagation();
    this.editClient.set({ ...client }); // spread = fresh copy, avoids mutation
    this.showModal.set(true);
  }

  closeModal(): void {
    this.showModal.set(false);
    this.editClient.set(null);
  }

  // Called by ClientFormComponent when save succeeds
  onSaved(): void {
    this.closeModal();
    this.load(); // reload list to show changes immediately
  }

  delete(client: Client, event: Event): void {
    event.stopPropagation();
    if (!confirm(`Delete ${client.display_name ?? client.name}?`)) return;
    this.clientService.destroy(client.id).subscribe(() => this.load());
  }
}