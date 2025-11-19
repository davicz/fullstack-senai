import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink, Router } from '@angular/router';
import { Auth } from '../../services/auth';

@Component({
  selector: 'app-appLayout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink],
  templateUrl: './app.html',
  styleUrl: './app.css',
})
export class AppLayoutComponent implements OnInit {

  user: any = null;
  userRoles: string[] = [];
  userHasMultipleProfiles = false;
  
  // Mapeamento de slug para nome amigável
  roleNames: {[key: string]: string} = {
    'national_admin': 'Administrador Nacional',
    'regional_admin': 'Administrador Regional',
    'unit_admin': 'Administrador Escolar',
    'teacher': 'Docente',
    'student': 'Estudante'
  };

  constructor(
    private auth: Auth,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.auth.currentUser.subscribe(user => {
      if (user) {
        this.user = user;
        // Verificação de segurança: garante que roles é um array
        if (Array.isArray(user.roles)) {
            this.userRoles = user.roles.map((role: any) => role.slug);
            this.userHasMultipleProfiles = user.roles.length > 1;
        } else {
            this.userRoles = [];
            this.userHasMultipleProfiles = false;
        }
      }
    });
  }

  hasRole(slug: string): boolean {
    return this.userRoles.includes(slug);
  }

  isAdmin(): boolean {
    return this.userRoles.some(slug => 
      ['national_admin', 'regional_admin', 'unit_admin'].includes(slug)
    );
  }
  
  // FUNÇÃO CORRIGIDA E SEGURA
  getCurrentRoleName(): string {
    // 1. Se não tiver usuário carregado ainda
    if (!this.user) return 'Carregando...';

    // 2. Tenta pegar o perfil selecionado
    let slug = this.user.selected_role;

    // 3. Fallback: Se não tiver perfil selecionado explícito, usa o primeiro da lista de roles
    if (!slug && this.userRoles.length > 0) {
        slug = this.userRoles[0];
    }

    // 4. Validação de Tipo: Só tenta fazer replace se for texto (string)
    if (typeof slug === 'string') {
        // Retorna o nome amigável do mapa ou formata o slug removendo underline
        return this.roleNames[slug] || slug.replace(/_/g, ' ').toUpperCase();
    }
    
    // 5. Se chegou aqui (é nulo, número ou objeto desconhecido), retorna um genérico para não travar a tela
    return 'Perfil Ativo';
  }

  changeProfile(): void {
    this.auth.logoutToProfileSelection();
  }

  logout(): void {
    this.auth.logout();
  }
}