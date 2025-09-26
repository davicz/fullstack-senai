import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CollaboratorService } from '../../../services/collaborator';
import { PageEvent } from '@angular/material/paginator';
import { FormControl } from '@angular/forms';
import { debounceTime, distinctUntilChanged } from 'rxjs';
import { MatDialog } from '@angular/material/dialog';
import { EditUserDialog } from '../../../components/edit-user-dialog/edit-user-dialog';
import { HeaderDashboard } from '../../../components/header-dashboard/header-dashboard';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    HeaderDashboard,
    RouterModule
  ],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.css',
})
export class Dashboard implements OnInit {
  collaborators: any[] = [];
  displayedColumns: string[] = ['id', 'name', 'email', 'cpf', 'actions'];

  // Controles de Paginação e Busca
  totalItems = 0;
  currentPage = 1;
  pageSize = 15;
  searchControl = new FormControl('');

  constructor(
    private collaboratorService: CollaboratorService,
    public dialog: MatDialog
  ) {}

  ngOnInit(): void {
    this.loadCollaborators();

    // Escuta as mudanças no campo de busca
    this.searchControl.valueChanges
      .pipe(
        debounceTime(300), // Espera 300ms após o usuário parar de digitar
        distinctUntilChanged() // Só emite se o valor for diferente do anterior
      )
      .subscribe((value) => {
        this.currentPage = 1; // Volta para a primeira página ao buscar
        this.loadCollaborators(value || '');
      });
  }

  openEditDialog(user: any): void {
    const dialogRef = this.dialog.open(EditUserDialog, {
      width: '500px',
      data: { user: user }, // Passa os dados do usuário para o dialog
    });

    dialogRef.afterClosed().subscribe((result) => {
      if (result) {
        // Se o dialog retornou 'true', significa que algo foi salvo.
        // Recarregamos a lista para mostrar as atualizações.
        this.loadCollaborators(this.searchControl.value || '');
      }
    });
  }

  loadCollaborators(search: string = ''): void {
    this.collaboratorService
      .getCollaborators(this.currentPage, search)
      .subscribe((response) => {
        this.collaborators = response.data;
        this.totalItems = response.total;
        this.pageSize = response.per_page;
      });
  }

  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex + 1;
    this.loadCollaborators(this.searchControl.value || '');
  }
}
