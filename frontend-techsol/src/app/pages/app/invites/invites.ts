// src/app/pages/app/invites/invites.ts
import { Component, OnInit, OnDestroy } from '@angular/core'; // REMOVIDO NgZone
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { InviteService } from '../../../services/invite';
import { Auth } from '../../../services/auth';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-invites',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './invites.html',
  styleUrl: './invites.css',
})
export class Invites implements OnInit, OnDestroy {
  // Lista de convites enviados
  sentInvites: any[] = [];
  isListLoading = false;
  listError: string | null = null;

  // modal / wizard
  isModalOpen = false;

  isDetailsModalOpen = false;
  selectedInvite: any = null;

  currentStep = 1; // 1..4

  // form
  inviteEmail = '';
  inviteCpf = '';

  // chosen roles map
  selectedProfiles: { [key: number]: boolean } = {};

  // backend lists
  operationalUnits: Array<{ id: number; name: string; regional_department_id?: number | null }> = [];
  regionalDepartments: Array<{ id: number; name: string }> = [];

  // displayed after filtering
  operationalUnitsDisplayed: Array<{ id: number; name: string; regional_department_id?: number | null }> = [];

  // when admin-escola selected (single selection enforced)
  selectedAdminSchoolIds: { [key: number]: boolean } = {};

  // context for national->region->school flow
  selectedRegionalDepartmentId: number | null = null;
  selectedOperationalUnitId: number | null = null;

  currentInvitationId: number | null = null;

  // feedback
  isLoading = false;
  errorMessage: string | null = null;
  fieldErrors: any = null;
  successMessage: string | null = null;

  // perfis (static for now)
  allProfiles = [
    { id: 1, name: 'Administrador SENAI', slug: 'national_admin', description: 'Visualizar dados de todo Brasil.' },
    { id: 2, name: 'Administrador Regional', slug: 'regional_admin', description: 'Gerenciar escolas e docentes do estado.' },
    { id: 3, name: 'Administrador Escola', slug: 'unit_admin', description: 'Adicionar docentes e gerenciar turmas.' },
    { id: 4, name: 'Docente', slug: 'teacher', description: 'Agendar e aplicar avaliações.' },
    { id: 5, name: 'Aluno', slug: 'student', description: 'Responder avaliações.' }
  ];

  // fallback
  fallbackSchools = [
    { id: 10, name: 'Operacional Poço', regional_department_id: 1 },
    { id: 11, name: 'Operacional - CIC', regional_department_id: 1 },
    { id: 12, name: 'Operacional Bauru', regional_department_id: 2 }
  ];

  // runtime
  private subs: Subscription[] = [];
  currentUser: any = null;
  currentUserRoleSlug = '';

  constructor(
    private inviteService: InviteService,
    private auth: Auth
    // REMOVIDO private zone: NgZone
  ) {}

  ngOnInit(): void {
    this.loadCurrentUser();

    this.loadSentInvites(); // Carrega a lista de convites ao iniciar

    if (this.currentUserRoleSlug === 'national_admin') {
      this.loadRegionalDepartments();
      this.loadOperationalUnits();
    } else if (this.currentUserRoleSlug === 'regional_admin') {
      this.loadOperationalUnits();
    }
  }

  ngOnDestroy(): void {
    this.subs.forEach(s => s.unsubscribe());
  }

  // -------------------------
  // current user helpers
  // -------------------------
  private loadCurrentUser() {
    try {
      const raw = (typeof window !== 'undefined' && localStorage.getItem('siac_user')) || null;
      if (raw) {
        this.currentUser = JSON.parse(raw);
        this.currentUserRoleSlug = this.currentUser?.selected_role?.slug || this.currentUser?.roles?.[0]?.slug || '';
      }
    } catch (err) {
      console.error('Erro ao ler siac_user', err);
      this.currentUser = null;
      this.currentUserRoleSlug = '';
    }
  }

  // -------------------------
  // open/close modal
  // -------------------------
  openInviteModal() {
    this.isModalOpen = true;
    this.currentStep = 1;
    this.inviteEmail = '';
    this.inviteCpf = '';
    this.selectedProfiles = {};
    this.selectedAdminSchoolIds = {};
    this.selectedRegionalDepartmentId = null;
    this.selectedOperationalUnitId = null;
    this.currentInvitationId = null;
    this.errorMessage = null;
    this.fieldErrors = null;
    this.successMessage = null;

    if (this.currentUserRoleSlug === 'national_admin') {
      this.loadRegionalDepartments();
      this.loadOperationalUnits();
    } else if (this.currentUserRoleSlug === 'regional_admin') {
      this.loadOperationalUnits();
    }

    this.operationalUnitsDisplayed = [...this.operationalUnits];
  }

  closeInviteModal() {
    this.isModalOpen = false;
  }

  searchTerm: string = '';
  filteredInvites: any[] = [];

  filterInvites() {
    const term = this.searchTerm.toLowerCase().trim();

    if (!term) {
      this.filteredInvites = [...this.sentInvites];
      return;
    }

    this.filteredInvites = this.sentInvites.filter(inv =>
      inv.email.toLowerCase().includes(term) ||
      inv.cpf.includes(term)
    );
  }

  // [INÍCIO DA ADIÇÃO]
  // -------------------------
  // list loader (convites)
  // -------------------------
  public loadSentInvites() {
    this.isListLoading = true;
    this.listError = null;

    const sub = this.inviteService.getInvites().subscribe({
      next: (resp) => {
        // A API retorna um paginador: { data: [...] }
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.sentInvites = data;
        } else {
          console.warn('Resposta de convites inesperada', resp);
          this.sentInvites = [];
        }
        this.filteredInvites = [...this.sentInvites];
        this.isListLoading = false;
      },
      error: (err) => {
        console.error('Erro ao carregar convites:', err);
        this.listError = 'Falha ao carregar convites enviados.';
        this.isListLoading = false;
      }
    });
    this.subs.push(sub);
  }

  // [FIM DA ADIÇÃO]

 translateStatus(status: string): string {
  switch (status) {
    case 'pending':
    case 'sent': 
      return 'Pendente';

    // --- FINALIZADOS ---
    case 'accepted':
    case 'completed':
      return 'Aceito'; 

    // --- INVALIDADOS ---
    case 'cancelled':
      return 'Cancelado';
    
    case 'expired':
      return 'Expirado'; // O prazo de 24h acabou

    case 'step_1_pending_profiles':
      return 'Rascunho (Faltam Perfis)';
    
    case 'step_2_pending_context':
    case 'step_3_pending_confirmation':
      return 'Rascunho (Não enviado)';

    default:
      return status || '—';
  }
}

 openInviteDetails(invite: any) {
    // 1. Extrai as informações de relacionamento (Roles e Contexto)
    const role = invite.roles?.[0];
    const ctx = role?.pivot ?? {};

    // 2. Resolve os nomes (Região e Escola) usando os métodos que você já tem
    const regName = ctx.regional_department_id
      ? this.getRegionalDepartmentName(ctx.regional_department_id)
      : null;

    const unitName = ctx.operational_unit_id
      ? this.getOperationalUnitName(ctx.operational_unit_id)
      : null;

    // 3. Monta o objeto selectedInvite com tudo que o HTML precisa
    this.selectedInvite = {
      ...invite,
      role_name: role?.name || '—',
      regional_department: regName ? { name: regName } : null,
      operational_unit: unitName ? { name: unitName } : null
    };

    // 4. Abre o modal
    this.isDetailsModalOpen = true;
  }

  closeDetailsModal() {
    this.isDetailsModalOpen = false;
    this.selectedInvite = null;
  }

  // Função auxiliar para verificar validade (usada no HTML)
  isExpired(dateStr: string): boolean {
    if (!dateStr) return false;
    return new Date(dateStr) < new Date();
  }

  // AÇÃO DE CANCELAR
  cancelInvite(invite: any) {
    if (!invite || !invite.id) return;

    // Confirmação simples nativa
    if (!confirm(`Tem certeza que deseja cancelar o convite para ${invite.email}?`)) {
      return;
    }

    this.isLoading = true; // Reutilizando sua variável de loading global ou crie uma isDetailsLoading

    this.inviteService.cancelInvite(invite.id).subscribe({
      next: () => {
        this.isLoading = false;
        alert('Convite cancelado com sucesso!');
        
        this.closeDetailsModal(); // Fecha o modal
        this.loadSentInvites();   // Recarrega a lista para atualizar o status
      },
      error: (err: any) => {
        console.error('Erro ao cancelar convite', err);
        this.isLoading = false;
        alert('Erro ao cancelar o convite. Tente novamente.');
      }
    });
  }

  // -------------------------
  // list loaders
  // -------------------------
  private loadOperationalUnits() {
    const sub = this.inviteService.getOperationalUnits().subscribe({
      next: (resp) => {
        // SEM NgZone
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.operationalUnits = data.map((u: any) => ({
            id: u.id,
            name: u.name,
            regional_department_id: u.regional_department_id ?? null
          }));
        } else {
          this.operationalUnits = [];
        }
        // Correção do bug do fallback (mantida)
        this.operationalUnitsDisplayed = [...this.operationalUnits];
      },
      error: (err) => {
        // SEM NgZone
        console.warn('Operacional units load failed, using fallback', err);
        this.operationalUnits = this.fallbackSchools as any;
        this.operationalUnitsDisplayed = [...this.fallbackSchools];
      }
    });
    this.subs.push(sub);
  }

  private loadRegionalDepartments() {
    const sub = this.inviteService.getRegionalDepartments().subscribe({
      next: (resp) => {
        // SEM NgZone
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.regionalDepartments = data.map((r: any) => ({ id: r.id, name: r.name }));
        } else {
          this.regionalDepartments = [];
        }
      },
      error: (err) => {
        // SEM NgZone
        console.warn('Regional departments load failed', err);
        this.regionalDepartments = [];
      }
    });
    this.subs.push(sub);
  }

  goToNextStep() {
    this.errorMessage = null;
    this.fieldErrors = null;
    this.successMessage = null;

    if (this.currentStep === 1) {
      if (!this.inviteEmail || !this.inviteCpf) {
        this.errorMessage = 'Preencha e-mail e CPF antes de prosseguir.';
        return;
      }
      this.startInvite();
      return;
    }

    if (this.currentStep === 2) {
      const chosen = Object.keys(this.selectedProfiles).filter(k => this.selectedProfiles[+k]).map(k => +k);
      if (chosen.length === 0) {
        this.errorMessage = 'Selecione ao menos um perfil.';
        return;
      }
      const blocked = this.getBlockedProfilesForCurrentUser();
      for (const p of chosen) {
        if (blocked.includes(p)) {
          this.errorMessage = 'Você não tem permissão para convidar o perfil selecionado.';
          return;
        }
      }
      const needsContext = this.clientNeedsContextForSelectedRoles(chosen);
      if (!needsContext) {
        this.assignRolesAndSkipContext();
        return;
      }
      this.assignRoles();
      return;
    }

    if (this.currentStep === 3) {
      if ((this.selectedProfiles[3] || this.selectedProfiles[4] || this.selectedProfiles[5]) && !this.hasSelectedSchools()) {
        if (this.currentUserRoleSlug === 'regional_admin' || (this.currentUserRoleSlug === 'national_admin' && this.selectedRegionalDepartmentId)) {
          this.errorMessage = 'Selecione pelo menos uma escola.';
          return;
        }
      }
      if (this.currentUserRoleSlug === 'national_admin' && (this.selectedProfiles[2] || this.selectedProfiles[3] || this.selectedProfiles[4] || this.selectedProfiles[5]) && !this.selectedRegionalDepartmentId) {
        this.errorMessage = 'Selecione a Região antes de prosseguir.';
        return;
      }
      this.assignContext();
      return;
    }

    if (this.currentStep < 4) {
      this.currentStep++;
    }
  }

  goToPreviousStep() {
    const chosen = Object.keys(this.selectedProfiles).filter(k => this.selectedProfiles[+k]).map(k => +k);
    if (this.currentStep === 4 && !this.clientNeedsContextForSelectedRoles(chosen)) {
      this.currentStep = 2;
    } else if (this.currentStep > 1) {
      this.currentStep--;
    }
    this.errorMessage = null;
    this.fieldErrors = null;
    this.successMessage = null;
  }

  // -------------------------
  // API actions
  // -------------------------
  private startInvite() {
    this.isLoading = true;
    this.errorMessage = null;
    this.fieldErrors = null;
    const safety = setTimeout(() => {
      if (this.isLoading) {
        console.warn('startInvite: safety timeout clearing isLoading');
        this.isLoading = false;
      }
    }, 10000);

    const sub = this.inviteService.startInvite({ email: this.inviteEmail, cpf: this.inviteCpf }).subscribe({
      next: (inv) => {
        // SEM NgZone
        clearTimeout(safety);
        console.log('startInvite success', inv);
        this.currentInvitationId = inv.id;
        this.isLoading = false;
        this.currentStep = 2; // Correção do bug do setTimeout (mantida)
      },
      error: (err) => {
        // SEM NgZone
        clearTimeout(safety);
        console.error('Erro startInvite', err);
        this.isLoading = false;
        this.errorMessage = err?.error?.message ?? 'Erro ao criar convite.';
        this.fieldErrors = err?.error?.errors ?? null;
      }
    });
    this.subs.push(sub);
  }

  private assignRoles() {
    if (!this.currentInvitationId) {
      this.errorMessage = 'ID do convite ausente. Reinicie o processo.';
      return;
    }
    this.isLoading = true;
    const safety = setTimeout(() => { if (this.isLoading) { console.warn('assignRoles safety'); this.isLoading = false; } }, 10000);
    const roles = Object.keys(this.selectedProfiles).filter(k => this.selectedProfiles[+k]).map(k => +k);

    const sub = this.inviteService.assignRoles(this.currentInvitationId, roles).subscribe({
      next: (inv) => {
        // SEM NgZone
        clearTimeout(safety);
        this.isLoading = false;
        const needsContext = this.clientNeedsContextForSelectedRoles(roles);
        this.currentStep = needsContext ? 3 : 4;
      },
      error: (err) => {
        // SEM NgZone
        clearTimeout(safety);
        console.error('Erro assignRoles', err);
        this.isLoading = false;
        this.errorMessage = err?.error?.message ?? 'Erro ao associar perfis.';
        this.fieldErrors = err?.error?.errors ?? null;
      }
    });
    this.subs.push(sub);
  }

  private assignRolesAndSkipContext() {
    if (!this.currentInvitationId) {
      this.errorMessage = 'ID do convite ausente. Reinicie o processo.';
      return;
    }
    this.isLoading = true;
    const safety = setTimeout(() => { if (this.isLoading) { console.warn('assignRoles(skip) safety'); this.isLoading = false; } }, 10000);
    const roles = Object.keys(this.selectedProfiles).filter(k => this.selectedProfiles[+k]).map(k => +k);

    const sub = this.inviteService.assignRoles(this.currentInvitationId, roles).subscribe({
      next: () => {
        // SEM NgZone
        clearTimeout(safety);
        this.isLoading = false;
        this.currentStep = 4;
      },
      error: (err) => {
        // SEM NgZone
        clearTimeout(safety);
        console.error('Erro assignRoles (skip)', err);
        this.isLoading = false;
        this.errorMessage = err?.error?.message ?? 'Erro ao associar perfis.';
        this.fieldErrors = err?.error?.errors ?? null;
      }
    });
    this.subs.push(sub);
  }

  private assignContext() {
    if (!this.currentInvitationId) {
      this.errorMessage = 'ID do convite ausente.';
      return;
    }
    this.isLoading = true;
    const safety = setTimeout(() => { if (this.isLoading) { console.warn('assignContext safety'); this.isLoading = false; } }, 10000);
    const assignments: any[] = [];
    const selectedSchoolId = Object.keys(this.selectedAdminSchoolIds).filter(k => this.selectedAdminSchoolIds[+k]).map(k => +k)[0] ?? null;

    if (this.selectedProfiles[3]) {
      assignments.push({
        role_id: 3,
        operational_unit_id: selectedSchoolId,
        regional_department_id: this.selectedRegionalDepartmentId ?? null
      });
    }
    if (this.selectedProfiles[2]) {
      if (this.selectedRegionalDepartmentId) {
        assignments.push({
          role_id: 2,
          regional_department_id: this.selectedRegionalDepartmentId
        });
      }
    }
    const otherRoles = [4, 5];
    otherRoles.forEach(rid => {
      if (this.selectedProfiles[rid]) {
        assignments.push({
          role_id: rid,
          operational_unit_id: selectedSchoolId,
          regional_department_id: this.selectedRegionalDepartmentId ?? null
        });
      }
    });

    const sub = this.inviteService.assignContext(this.currentInvitationId, assignments).subscribe({
      next: () => {
        // SEM NgZone
        clearTimeout(safety);
        this.isLoading = false;
        this.currentStep = 4;
      },
      error: (err) => {
        // SEM NgZone
        clearTimeout(safety);
        console.error('Erro assignContext', err);
        this.errorMessage = err?.error?.message ?? 'Erro ao atribuir contexto.';
        this.fieldErrors = err?.error?.errors ?? null;
        this.isLoading = false;
      }
    });
    this.subs.push(sub);
  }

  sendInvite() {
    if (!this.currentInvitationId) {
      this.errorMessage = 'ID do convite ausente.';
      return;
    }
    this.isLoading = true;
    const safety = setTimeout(() => { if (this.isLoading) { console.warn('sendInvite safety'); this.isLoading = false; } }, 10000);

    const sub = this.inviteService.sendInvite(this.currentInvitationId).subscribe({
      next: () => {
        // SEM NgZone
        clearTimeout(safety);
        this.isLoading = false;
        this.successMessage = 'Convite enviado com sucesso!';
        setTimeout(() => this.closeInviteModal(), 900);
      },
      error: (err) => {
        // SEM NgZone
        clearTimeout(safety);
        console.error('Erro sendInvite', err);
        this.errorMessage = err?.error?.message ?? 'Erro ao enviar convite.';
        this.fieldErrors = err?.error?.errors ?? null;
        this.isLoading = false;
      }
    });
    this.subs.push(sub);
  }

  // -------------------------
  // UI helpers
  // -------------------------
  getSelectedProfileNames(): string[] {
    return this.allProfiles.filter(p => this.selectedProfiles[p.id]).map(p => p.name);
  }

  hasSelectedSchools(): boolean {
    return Object.keys(this.selectedAdminSchoolIds)
      .some(k => this.selectedAdminSchoolIds[+k]);
  }

  toggleSelectAdminSchool(id: number) {
    if (this.selectedAdminSchoolIds[id]) {
      delete this.selectedAdminSchoolIds[id];
      this.selectedOperationalUnitId = null;
      return;
    }
    Object.keys(this.selectedAdminSchoolIds).forEach(k => delete this.selectedAdminSchoolIds[+k]);
    this.selectedAdminSchoolIds[id] = true;
    this.selectedOperationalUnitId = id;
  }

  getSelectedSchoolNames(): string[] {
    const list = (this.operationalUnits && this.operationalUnits.length) ? this.operationalUnits : this.fallbackSchools;
    return list.filter((s: any) => this.selectedAdminSchoolIds[s.id]).map((s: any) => s.name);
  }

  getRegionalDepartmentName(id: number | null): string {
    if (!id) return '—';
    const r = this.regionalDepartments.find(x => x.id === id);
    return r ? r.name : '—';
  }

  getOperationalUnitName(id: number | null): string {
    if (!id) return '—';
    const u = this.operationalUnits.find(x => x.id === id) || (this.fallbackSchools as any).find((x: any) => x.id === id);
    return u ? u.name : '—';
  }

  onRegionalDepartmentChange() {
    if (this.selectedRegionalDepartmentId) {
      this.operationalUnitsDisplayed = this.operationalUnits.filter(u => u.regional_department_id === this.selectedRegionalDepartmentId);
    } else {
      this.operationalUnitsDisplayed = [...this.operationalUnits];
    }
    this.selectedAdminSchoolIds = {};
    this.selectedOperationalUnitId = null;
  }

  getBlockedProfilesForCurrentUser(): number[] {
    if (this.currentUserRoleSlug === 'national_admin') return [];
    if (this.currentUserRoleSlug === 'regional_admin') return [1, 2];
    if (this.currentUserRoleSlug === 'unit_admin') return [1, 2, 3];
    return [1, 2, 3, 4, 5];
  }

  private clientNeedsContextForSelectedRoles(chosenRoleIds: number[]): boolean {
    if (this.currentUserRoleSlug === 'national_admin') {
      return chosenRoleIds.some(r => [2,3,4,5].includes(r));
    }
    if (this.currentUserRoleSlug === 'regional_admin') {
      return chosenRoleIds.some(r => [3,4,5].includes(r));
    }
    if (this.currentUserRoleSlug === 'unit_admin') {
      return false;
    }
    return false;
  }
}