// src/app/pages/app/users/users.ts

// 1. Importe 'Inject', 'PLATFORM_ID' e 'isPlatformBrowser'
import { Component, OnInit, ChangeDetectorRef, Inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { User } from '../../../services/user';
import { Subject } from 'rxjs';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';

@Component({
  selector: 'app-users',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './users.html',
  styleUrls: ['./users.css'],
})
export class Users implements OnInit {
  users: any[] = [];
  isLoading = true; // Deixe true por padrão
  errorMessage: string | null = null;

  // Filtros
  searchTerm = '';
  selectedRoleId: string = '';

  // Modal de Edição
  isEditModalOpen = false;
  editingUser: any = null;
  isSaving = false;

  // Perfis fixos (por enquanto)
  allRoles = [
    { id: 1, name: 'Administrador Senai', slug: 'national_admin', level: 4 },
    { id: 2, name: 'Administrador Regional', slug: 'regional_admin', level: 3 },
    { id: 3, name: 'Administrador Escola', slug: 'unit_admin', level: 2 },
    { id: 4, name: 'Docente', slug: 'teacher', level: 1 },
    { id: 5, name: 'Aluno', slug: 'student', level: 0 }
  ];

  // Hierarquia do logado
  currentUser: any = null;
  currentRoleLevel = 0;

  constructor(
    private userService: User,
    private cdr: ChangeDetectorRef,
    // 2. Injete o PLATFORM_ID
    @Inject(PLATFORM_ID) private platformId: Object
  ) {}

  ngOnInit(): void {
    // 3. Adicione o "Guarda"
    // Este código SÓ roda no navegador
    if (isPlatformBrowser(this.platformId)) {
      this.loadCurrentUser();
      // O setTimeout agora está seguro dentro do "guarda"
      setTimeout(() => this.loadUsers(), 10);
    } 
    // 4. Se estiver no servidor (else)
    else {
      // Não fazemos nada que dependa do browser (localStorage ou API).
      // A página SSR será renderizada vazia (ou com o 'isLoading'), o que é correto.
      console.debug('[UsersComponent] Running on Server. Skipping localStorage and API calls.');
    }
  }

  private loadCurrentUser(): void {
    // 5. O código aqui dentro agora é SEGURO
    // porque o ngOnInit só o chama se estiver no browser.
    try {
      const storedUser = localStorage.getItem('siac_user'); 
      if (storedUser) {
        this.currentUser = JSON.parse(storedUser);
        const currentRoleSlug = this.currentUser?.selected_role?.slug || this.currentUser?.roles?.[0]?.slug;
        const roleObj = this.allRoles.find(r => r.slug === currentRoleSlug);
        this.currentRoleLevel = roleObj ? roleObj.level : 0;
        console.debug('[UsersComponent] currentUser', this.currentUser, 'roleLevel', this.currentRoleLevel);
      } else {
        console.debug('[UsersComponent] no siac_user in localStorage');
      }
    } catch (err) {
      console.error('Erro ao carregar usuário logado:', err);
    }
  }

  loadUsers(): void {
    this.isLoading = true;
    this.errorMessage = null;
    this.users = []; 

    const roleParam = (this.selectedRoleId === '' ? null : this.selectedRoleId);
    const searchParam = (this.searchTerm.trim() === '' ? null : this.searchTerm.trim());

    console.debug('[UsersComponent] loadUsers ->', { page: 1, searchParam, roleParam });

    this.userService.getUsers(1, searchParam, roleParam).subscribe({
      next: (response) => {
        try {
          const data = response?.data ?? response;
          if (!data) {
            console.warn('[UsersComponent] resposta vazia', response);
            this.users = [];
          } else if (Array.isArray(data)) {
            this.users = data;
          } else if (data.data && Array.isArray(data.data)) {
            this.users = data.data;
          } else if (data.items && Array.isArray(data.items)) {
            this.users = data.items;
          } else {
            this.users = Array.isArray(data) ? data : [data];
          }
          console.debug('[UsersComponent] users loaded count=', this.users.length);
        } catch (err) {
          console.error('[UsersComponent] erro ao processar resposta', err, response);
          this.users = [];
          this.errorMessage = 'Erro ao processar a resposta da API.';
        }

        this.isLoading = false;
        try { this.cdr.detectChanges(); } catch(e){ /* ok */ }
      },
      error: (err) => {
        console.error('Erro ao carregar usuários:', err);
        this.errorMessage = err?.error?.message ?? 'Não foi possível carregar os usuários.';
        this.isLoading = false;
        try { this.cdr.detectChanges(); } catch(e){ /* ok */ }
      },
    });
  }

  getRoleNames(roles: any[]): string {
    if (!roles || !Array.isArray(roles) || roles.length === 0) return '—';
    return roles.map(role => role.name).join(', ');
  }

getRoleBadgeClass(roles: any[]): string {
  if (!roles || !Array.isArray(roles) || roles.length === 0) {
    return 'bg-gray-100 text-gray-800';
  }

  const roleNames = roles.map(r => (r.slug || r.name).toLowerCase());

  if (roleNames.some(r => r.includes('national') || r.includes('nacional') || r.includes('Senai'))) {
    return 'bg-red-100 text-red-800';
  }
  if (roleNames.some(r => r.includes('regional'))) {
    return 'bg-orange-100 text-orange-800';
  }
  if (roleNames.some(r => r.includes('school') || r.includes('escola') || r.includes('unit'))) {
    return 'bg-blue-100 text-blue-800';
  }
  if (roleNames.some(r => r.includes('teacher') || r.includes('docente') || r.includes('professor'))) {
    return 'bg-yellow-100 text-yellow-800';
  }
  if (roleNames.some(r => r.includes('student') || r.includes('aluno'))) {
    return 'bg-green-100 text-green-800';
  }
  return 'bg-gray-100 text-gray-800';
}

  // --- MODAL DE EDIÇÃO ---

  openEditModal(user: any) {
    this.editingUser = JSON.parse(JSON.stringify(user));
    this.editingUser.roleIds = Array.isArray(this.editingUser.roles) ? this.editingUser.roles.map((r: any) => r.id) : [];
    this.isEditModalOpen = true;
  }

  hasRole(roleId: number): boolean {
    return this.editingUser?.roleIds?.includes(roleId);
  }

  toggleRole(roleId: number, event: any) {
    const role = this.allRoles.find(r => r.id === roleId);
    if (!role) return;

    if (role.level > this.currentRoleLevel) {
      alert('Você não pode atribuir um perfil de nível superior ao seu.');
      event.target.checked = false;
      return;
    }

    if (event.target.checked) {
      this.editingUser.roleIds.push(roleId);
    } else {
      this.editingUser.roleIds = this.editingUser.roleIds.filter((id: number) => id !== roleId);
    }
  }

  closeEditModal() {
    this.isEditModalOpen = false;
    this.editingUser = null;
  }

    saveUser() {
    if (!this.editingUser) return;
    this.isSaving = true;

    const payload = {
      name: this.editingUser.name,
      roles: this.editingUser.roleIds
    };

    this.userService.updateUser(this.editingUser.id, payload).subscribe({
      next: (resp) => {
        alert('Usuário atualizado com sucesso!');
        this.isSaving = false;
        this.closeEditModal();
        this.loadUsers();
      },
      error: (err) => {
        console.error(err);
        alert('Erro ao atualizar: ' + (err.error?.message || 'Erro desconhecido'));
        this.isSaving = false;
      }
    });
  }
}