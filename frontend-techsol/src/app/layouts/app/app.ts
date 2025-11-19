import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink, Router, NavigationEnd } from '@angular/router';
import { Auth } from '../../services/auth';
import { filter } from 'rxjs/operators';

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
  
  // CONTROLE DO MENU MOBILE
  isSidebarOpen = false;

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
  ) {
    // Fecha o menu automaticamente ao trocar de rota (útil no mobile)
    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe(() => {
      this.isSidebarOpen = false;
    });
  }

  ngOnInit(): void {
    this.auth.currentUser.subscribe(user => {
      if (user) {
        this.user = user;
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
  
  getCurrentRoleName(): string {
    if (!this.user) return 'Carregando...';

    let slug = this.user.selected_role;

    if (!slug && this.userRoles.length > 0) {
        slug = this.userRoles[0];
    }

    if (typeof slug === 'string') {
        return this.roleNames[slug] || slug.replace(/_/g, ' ').toUpperCase();
    }
    
    return 'Perfil Ativo';
  }

  changeProfile(): void {
    this.auth.logoutToProfileSelection();
  }

  logout(): void {
    this.auth.logout();
  }

  // FUNÇÃO PARA ABRIR/FECHAR MENU MOBILE
  toggleSidebar(): void {
    this.isSidebarOpen = !this.isSidebarOpen;
  }
}