import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink, Router } from '@angular/router';
import { Auth } from '../../services/auth'; // Importe nosso serviço

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

  constructor(
    private auth: Auth,
    private router: Router
  ) {}

  ngOnInit(): void {
    // Busca o usuário logado no AuthService
    this.auth.currentUser.subscribe(user => {
      // SÓ atualiza os dados se o 'user' não for nulo.
      if (user && user.roles) {
        this.user = user;
        this.userRoles = user.roles.map((role: any) => role.slug);
        this.userHasMultipleProfiles = user.roles.length > 1;
      }
      // A lógica 'else' foi removida. O AuthGuard já cuida de quem
      // não está logado. Se o 'user' for nulo no início, não fazemos nada
      // e esperamos o AuthGuard fazer seu trabalho na próxima navegação.
    });
  }

  // Funções helper para o HTML (para checar permissões)
  hasRole(slug: string): boolean {
    return this.userRoles.includes(slug);
  }

  isAdmin(): boolean {
    return this.userRoles.some(slug => 
      ['national_admin', 'regional_admin', 'unit_admin'].includes(slug)
    );
  }
  
  /**
   * Esta função faz o "Logout Suave"
   */
  changeProfile(): void {
    this.auth.logoutToProfileSelection();
  }

  /**
   * Esta função faz o "Logout Completo".
   */
  logout(): void {
    this.auth.logout();
  }
}