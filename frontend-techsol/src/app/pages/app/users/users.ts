import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { User } from '../../../services/user';

@Component({
  selector: 'app-users',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './users.html',
  styleUrls: ['./users.css'],
})
export class Users implements OnInit {
  users: any[] = [];
  isLoading = true;
  errorMessage: string | null = null;

  constructor(
    private userService: User,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    this.users = [];
    this.isLoading = true;
    this.loadUsers();
  }

  loadUsers(): void {
    this.isLoading = true;
    this.errorMessage = null;

    this.userService.getUsers().subscribe({
      next: (response) => {
        this.users = response?.data ?? response ?? [];
        this.isLoading = false;
        this.cdr.detectChanges(); // 👈 força a atualização da view
      },
      error: (err) => {
        console.error('Erro ao carregar usuários:', err);
        this.errorMessage =
          err?.error?.message ?? 'Não foi possível carregar os usuários.';
        this.isLoading = false;
        this.cdr.detectChanges(); // 👈 idem aqui
      },
    });
  }

  getRoleNames(roles: any[]): string {
    if (!roles || !Array.isArray(roles) || roles.length === 0) return '—';
    return roles.map(role => role.name).join(', ');
  }
}
