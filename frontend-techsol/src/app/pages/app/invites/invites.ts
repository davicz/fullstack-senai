import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-invites',
  imports: [CommonModule, FormsModule],
  templateUrl: './invites.html',
  styleUrl: './invites.css',
})
export class Invites {

  isModalOpen = false;
  currentStep = 1; // 1: Dados, 2: Perfis, 3: Escolas, 4: Confirmar

  inviteEmail = '';
  inviteCpf = '';

  // Perfis disponíveis para seleção

  allProfiles = [
    { id: 1, name: 'Administrador SENAI', description: 'Visualizar dados de todo Brasil.' },
    { id: 2, name: 'Administrador Regional', description: 'Gerenciar escolas e docentes do estado.' },
    { id: 3, name: 'Administrador Escola', description: 'Adicionar docentes e gerenciar turmas.' },
    { id: 4, name: 'Docente', description: 'Agendar e aplicar avaliações.' },
    { id: 5, name: 'Aluno', description: 'Responder avaliações de qualquer lugar.' },
  ];

  selectedProfiles: { [key: number]: boolean } = {};

  // Escolas disponíveis para seleção

  allSchools = [
    { id: 10, name: 'Operacional Poço' },
    { id: 11, name: 'Operacional - CIC' },
    { id: 12, name: 'Operacional Bauru' },
    { id: 13, name: 'Operacional Arapiraca' },
    { id: 14, name: 'Operacional Benedito Bentes' },
  ];

  selectedAdminSchoolIds: { [key: number]: boolean } = {};

  constructor() {}

  openInviteModal() {
    this.isModalOpen = true;
    this.currentStep = 1; // Reseta para a etapa 1
    this.inviteEmail = '';
    this.inviteCpf = '';
    this.selectedProfiles = {};
    this.selectedAdminSchoolIds = {};
  }

  closeInviteModal() {
    this.isModalOpen = false;
  }

  goToNextStep() {
    // Lógica futura:
    // Etapa 1 -> 2: POST /api/invites/start
    // Etapa 2 -> 3: POST /api/invites/{id}/roles
    // Etapa 3 -> 4: POST /api/invites/{id}/context
    if (this.currentStep < 4) {
      
      // Salto condicional: Se "Admin Escola" não foi selecionado, pule a Etapa 3
      if (this.currentStep === 2 && !this.isProfileSelected(3)) {
        console.log("Pulando etapa 3 (Atribuição de Escola)");
        this.currentStep = 4; // Pula para a confirmação
      } else {
        this.currentStep++;
      }
    }
  }

  goToPreviousStep() {
    if (this.currentStep > 1) {
      // Salto condicional reverso
      if (this.currentStep === 4 && !this.isProfileSelected(3)) {
        this.currentStep = 2; // Volta para a seleção de perfis
      } else {
        this.currentStep--;
      }
    }
  }

  sendInvite() {
    // Lógica final: POST /api/invites/{id}/send
    console.log('Enviando convite!');
    console.log('Dados:', this.inviteEmail, this.inviteCpf);
    console.log('Perfis:', this.getSelectedProfileNames());
    console.log('Escolas:', this.getSelectedSchoolNames());
    this.closeInviteModal();
  }

  // --- Métodos Auxiliares ---

  // Retorna true se um perfil específico (pelo ID) foi selecionado
  isProfileSelected(profileId: number): boolean {
    return !!this.selectedProfiles[profileId];
  }

  // Retorna os nomes dos perfis selecionados (para a tela de confirmação)
  getSelectedProfileNames(): string[] {
    return this.allProfiles
      .filter(p => this.selectedProfiles[p.id])
      .map(p => p.name);
  }

  // Retorna os nomes das escolas selecionadas
  getSelectedSchoolNames(): string[] {
    return this.allSchools
      .filter(s => this.selectedAdminSchoolIds[s.id])
      .map(s => s.name);
  }

}
