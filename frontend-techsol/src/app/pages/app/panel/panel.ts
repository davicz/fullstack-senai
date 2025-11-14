// src/app/pages/app/panel/panel.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-panel',
  standalone: true,
  imports: [CommonModule, RouterModule],  // <--- ADICIONE ISSO
  templateUrl: './panel.html',
  styleUrl: './panel.css',
})
export class Panel implements OnInit {

  user: any = null;
  roleSlug = '';

  stats: Array<{ label: string; value: number; icon: string; color: string }> = [];
  shortcuts: Array<{ label: string; route: string; icon: string }> = [];

  ngOnInit(): void {
    this.loadUser();
    this.loadDashboardData();
  }

  private loadUser() {
    try {
      const raw = localStorage.getItem('siac_user');
      if (raw) {
        this.user = JSON.parse(raw);
        this.roleSlug =
          this.user?.selected_role?.slug ||
          this.user?.roles?.[0]?.slug ||
          '';
      }
    } catch {
      this.user = null;
    }
  }

  private loadDashboardData() {
    if (!this.roleSlug) return;

    switch (this.roleSlug) {
      case 'national_admin':
        this.stats = [
          { label: 'Departamentos Regionais', value: 27, icon: '🏢', color: 'blue' },
          { label: 'Unidades Operacionais', value: 550, icon: '🏫', color: 'green' },
          { label: 'Usuários Cadastrados', value: 12000, icon: '👥', color: 'purple' },
        ];
        this.shortcuts = [
          { label: 'Gerenciar Usuários', route: '/app/users', icon: '👥' },
          { label: 'Criar Convite', route: '/app/invites', icon: '✉️' },
        ];
        break;

      case 'regional_admin':
        this.stats = [
          { label: 'Unidades no Estado', value: 12, icon: '🏫', color: 'green' },
          { label: 'Docentes', value: 220, icon: '📘', color: 'purple' },
          { label: 'Alunos Ativos', value: 1800, icon: '👨‍🎓', color: 'blue' },
        ];
        this.shortcuts = [
          { label: 'Convites', route: '/app/invites', icon: '✉️' },
          { label: 'Usuários', route: '/app/users', icon: '👥' },
        ];
        break;

      case 'unit_admin':
        this.stats = [
          { label: 'Turmas Abertas', value: 8, icon: '📚', color: 'blue' },
          { label: 'Docentes', value: 30, icon: '👩‍🏫', color: 'purple' },
          { label: 'Alunos', value: 480, icon: '👨‍🎓', color: 'green' },
        ];
        this.shortcuts = [
          { label: 'Gerenciar Usuários', route: '/app/users', icon: '👤' },
        ];
        break;

      case 'teacher':
        this.stats = [
          { label: 'Avaliações Pendentes', value: 3, icon: '📝', color: 'purple' },
          { label: 'Turmas', value: 5, icon: '📚', color: 'blue' },
        ];
        this.shortcuts = [];
        break;

      case 'student':
        this.stats = [
          { label: 'Avaliações Disponíveis', value: 2, icon: '📝', color: 'purple' },
          { label: 'Desempenho Geral', value: 87, icon: '📊', color: 'green' },
        ];
        this.shortcuts = [];
        break;
    }
  }
}
