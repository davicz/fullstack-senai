import { Component, Inject, PLATFORM_ID, OnInit } from '@angular/core';
import { isPlatformBrowser, CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { Auth } from '../../../services/auth';

interface RolePivot {
  user_id: number;
  role_id: number;
  id?: number;
  regional_department_id: number | null;
  operational_unit_id: number | null;
}

interface Role {
  id: number;
  name: string;
  slug: string;
  pivot?: RolePivot;
}

@Component({
  selector: 'app-profile-selector',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './profile-selector.html',
  styleUrls: ['./profile-selector.css'],
})
export class ProfileSelector implements OnInit {

  user: any = null;
  profiles: Role[] = [];
  selectedProfile: Role | null = null;
  selectedProfileName = '';
  isLoading = false;
  errorMessage: string | null = null;

  permissions: { [key: string]: string[] } = {
    'Administrador Senai': [
      'Visualizar dados de todo Brasil',
      'Criar novas escolas',
      'Adicionar novos administradores'
    ],
    'Administrador Regional': [
      'Gerenciar escolas e docentes do estado',
      'Agendar avaliações das escolas',
      'Exportar dados'
    ],
    'Administrador Escola': [
      'Adicionar docentes',
      'Gerenciar turmas',
      'Acompanhar desempenhos'
    ],
    'Docente': [
      'Agendar e aplicar avaliações',
      'Identificar capacidades a revisar',
      'Comparar rendimento'
    ],
    'Aluno': [
      'Responder avaliações',
      'Revisar desempenho',
      'Consultar avaliações anteriores'
    ],
  };

  constructor(
    private router: Router,
    private auth: Auth,
    @Inject(PLATFORM_ID) private platformId: Object
  ) {}

  ngOnInit(): void {
    // Garante que só roda no browser
    if (isPlatformBrowser(this.platformId)) {
      const storedUser = localStorage.getItem('siac_user');

      if (!storedUser) {
        this.auth.logout();
        return;
      }

      try {
        const userData = JSON.parse(storedUser);
        if (userData && userData.roles) {
          this.user = userData;
          this.profiles = userData.roles;

          if (this.profiles.length > 0) {
            // seleciona o primeiro perfil automaticamente
            this.selectProfile(this.profiles[0]);
          }
        } else {
          this.errorMessage = 'Usuário inválido ou sem perfis atribuídos.';
        }
      } catch (err) {
        console.error('Erro ao ler siac_user do localStorage:', err);
        this.auth.logout();
      }
    }
  }

  selectProfile(profile: Role) {
    this.selectedProfile = profile;
    this.selectedProfileName = profile.name;
    this.errorMessage = null;
  }

  navigateToApp() {
    if (!this.selectedProfile) return;

    this.isLoading = true;
    this.errorMessage = null;

    const roleIdToSend = this.selectedProfile.id;

    this.auth.selectProfile(roleIdToSend).subscribe({
      next: () => {
        this.isLoading = false;
        // O Auth já redireciona para /app/dashboard
      },
      error: (err) => {
        console.error('Erro ao selecionar perfil:', err);
        this.errorMessage = 'Não foi possível selecionar o perfil. Tente novamente.';
        this.isLoading = false;
      }
    });
  }

  logout(): void {
    this.auth.logout();
  }
}
