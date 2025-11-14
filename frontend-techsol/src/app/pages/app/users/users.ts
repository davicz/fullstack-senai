import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { User } from '../../../services/user';
import { Subject } from 'rxjs';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';

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

  // 2. Cria um "Tubo" para onde vamos jogar os termos digitados
  private searchSubject = new Subject<string>();

  constructor(
    private userService: User,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    this.users = [];
    this.isLoading = true;

    // 3. Configura o "ouvinte" da busca
    this.searchSubject.pipe(
      debounceTime(500), // Espera 500ms após a última digitação
      distinctUntilChanged() // Só busca se o texto for diferente do anterior
    ).subscribe((term) => {
      this.loadUsers(term); // Chama a API
    });

    // Carregamento inicial (sem busca)
    this.loadUsers();
  }

  // 4. Método que o HTML vai chamar a cada letra digitada
  onSearch(event: Event): void {
    const target = event.target as HTMLInputElement;
    this.searchSubject.next(target.value);
  }

  // 5. Atualizado para aceitar o termo
  loadUsers(searchTerm: string = ''): void {
    this.isLoading = true;
    this.errorMessage = null;

    // Passamos o termo para o service
    this.userService.getUsers(1, searchTerm).subscribe({
      next: (response) => {
        // Ajuste conforme seu backend (data ou raiz)
        this.users = response?.data ?? response ?? [];
        this.isLoading = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error('Erro ao carregar usuários:', err);
        this.errorMessage = err?.error?.message ?? 'Não foi possível carregar os usuários.';
        this.isLoading = false;
        this.cdr.detectChanges();
      },
    });
  }

  getRoleNames(roles: any[]): string {
    if (!roles || !Array.isArray(roles) || roles.length === 0) return '—';
    return roles.map(role => role.name).join(', ');
  }

  clearSearch(inputElement: HTMLInputElement): void {
    inputElement.value = ''; // Limpa o visual do input
    this.onSearch({ target: inputElement } as any); // Dispara a busca vazia para recarregar tudo
  }
}