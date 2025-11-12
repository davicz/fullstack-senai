import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-profile-selector',
  imports: [CommonModule],
  templateUrl: './profile-selector.html',
  styleUrl: './profile-selector.css',
})
export class ProfileSelector {
  
  profiles = [
    { id: 1, name: 'Administrador Senai' },
    { id: 2, name: 'Administrador Regional' },
    { id: 3, name: 'Administrador Escola' },
    { id: 4, name: 'Docente' },
    { id: 5, name: 'Aluno' },
  ];

  selectedProfileName = 'Administrador Senai';

  permissions = {
    'Administrador Senai': [
      'Visualizar dados de todo Brasil',
      'Criar novas escolas',
      'Adicionar novos administradores'
    ],
  };

  constructor(private router: Router) {}

  selectProfile(profileName: string) {
    this.selectedProfileName = profileName;
    // No futuro: this.apiService.selectProfile(profile.id)
  }

  navigateToApp() {
    // Simula a seleção de perfil (POST /api/login/profile)
    // e redireciona para a página principal do app
    this.router.navigate(['/app/dashboard']);
  }

}
