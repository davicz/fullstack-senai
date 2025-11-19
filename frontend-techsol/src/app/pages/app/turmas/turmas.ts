// src/app/pages/app/turmas/turmas.ts
import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { TurmaService } from '../../../services/turma';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-turmas',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: 'turmas.html',
  styleUrl: 'turmas.css',
})
export class Turmas implements OnInit, OnDestroy {
  // Lista de turmas
  turmas: any[] = [];
  filteredTurmas: any[] = [];
  isListLoading = false;
  listError: string | null = null;

  // Filtros
  filtroTurma = '';
  filtroOrigem = '';
  filtroTurno = '';
  filtroDR: number | null = null;
  filtroEscola = '';
  filtroCurso = '';
  filtroDocente = '';
  filtroAluno = '';

  // Listas para dropdowns
  origens: Array<{ id: number; name: string }> = [];
  turnos: Array<{ id: string; name: string }> = [];
  regionalDepartments: Array<{ id: number; name: string }> = [];
  operationalUnits: any[] = [];
  cursos: any[] = [];

  // Modal
  isModalOpen = false;
  isEditing = false;
  currentTurmaId: number | null = null;

  // Form data (somente campos editáveis)
  turmaForm = {
    name: '',
    codigo: '',
    origem: 'SIAC',
    turno: '',
    regional_department_id: null as number | null,
    operational_unit_id: null as number | null,
    course_id: null as number | null,
  };

  // Feedback
  isLoading = false;
  errorMessage: string | null = null;
  fieldErrors: any = null;
  successMessage: string | null = null;

  // Subscriptions
  private subs: Subscription[] = [];

  constructor(
    private turmaService: TurmaService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadTurmas();
    this.loadOrigens();
    this.loadTurnos();
    this.loadRegionalDepartments();
    this.loadOperationalUnits();
    this.loadCursos();
  }

  ngOnDestroy(): void {
    this.subs.forEach(s => s.unsubscribe());
  }

  // -------------------------
  // Carregar dados
  // -------------------------
  loadTurmas(): void {
    this.isListLoading = true;
    this.listError = null;

    const sub = this.turmaService.getTurmas().subscribe({
      next: (resp) => {
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.turmas = data;
        } else {
          console.warn('Resposta de turmas inesperada', resp);
          this.turmas = [];
        }
        this.filteredTurmas = [...this.turmas];
        this.isListLoading = false;
      },
      error: (err) => {
        console.error('Erro ao carregar turmas:', err);
        this.listError = 'Falha ao carregar turmas.';
        this.isListLoading = false;
      }
    });
    this.subs.push(sub);
  }

  loadOrigens(): void {
    const sub = this.turmaService.getOrigens().subscribe({
      next: (resp) => {
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.origens = data;
        } else if (Array.isArray(resp)) {
          this.origens = resp;
        }
      },
      error: (err) => console.warn('Erro ao carregar origens', err)
    });
    this.subs.push(sub);
  }

  loadTurnos(): void {
    const sub = this.turmaService.getTurnos().subscribe({
      next: (resp) => {
        if (Array.isArray(resp)) {
          this.turnos = resp as any;
        }
      },
      error: (err) => console.warn('Erro ao carregar turnos', err)
    });
    this.subs.push(sub);
  }

  loadRegionalDepartments(): void {
    const sub = this.turmaService.getRegionalDepartments().subscribe({
      next: (resp) => {
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.regionalDepartments = data;
        }
      },
      error: (err) => console.warn('Erro ao carregar regionais', err)
    });
    this.subs.push(sub);
  }

  loadOperationalUnits(): void {
    const sub = this.turmaService.getOperationalUnits().subscribe({
      next: (resp) => {
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.operationalUnits = data;
        }
      },
      error: (err) => console.warn('Erro ao carregar unidades operacionais', err)
    });
    this.subs.push(sub);
  }

  loadCursos(): void {
    const sub = this.turmaService.getCursos().subscribe({
      next: (resp) => {
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.cursos = data;
        }
      },
      error: (err) => console.warn('Erro ao carregar cursos', err)
    });
    this.subs.push(sub);
  }

  // -------------------------
  // Filtros
  // -------------------------
  aplicarFiltros(): void {
    let resultado = [...this.turmas];

    // Turma (nome ou código)
    if (this.filtroTurma.trim()) {
      const termo = this.filtroTurma.toLowerCase();
      resultado = resultado.filter(t =>
        t.name?.toLowerCase().includes(termo) ||
        t.codigo?.toLowerCase().includes(termo)
      );
    }

    // Origem (string)
    if (this.filtroOrigem) {
      resultado = resultado.filter(t => t.origem === this.filtroOrigem);
    }

    // Turno
    if (this.filtroTurno) {
      resultado = resultado.filter(t => t.turno === this.filtroTurno);
    }

    // DR
    if (this.filtroDR) {
      resultado = resultado.filter(t => {
        if (t.regional_department_id) {
          return t.regional_department_id === this.filtroDR;
        }
        // fallback: pegar do relacionamento da UO
        return t.operational_unit?.regional_department_id === this.filtroDR;
      });
    }

    // Escola
    if (this.filtroEscola.trim()) {
      const termo = this.filtroEscola.toLowerCase();
      resultado = resultado.filter(t =>
        t.operational_unit?.name?.toLowerCase().includes(termo)
      );
    }

    // Curso
    if (this.filtroCurso.trim()) {
      const termo = this.filtroCurso.toLowerCase();
      resultado = resultado.filter(t =>
        t.course?.name?.toLowerCase().includes(termo)
      );
    }

    // Docente
    if (this.filtroDocente.trim()) {
      const termo = this.filtroDocente.toLowerCase();
      resultado = resultado.filter(t => {
        const campo = this.getDocentesResponsaveis(t).toLowerCase();
        return campo.includes(termo);
      });
    }

    // Aluno (quantidade)
    if (this.filtroAluno.trim()) {
      const termo = this.filtroAluno.toLowerCase();
      resultado = resultado.filter(t => {
        const qtd = this.countAlunos(t).toString();
        return qtd.includes(termo);
      });
    }

    this.filteredTurmas = resultado;
  }

  limparFiltros(): void {
    this.filtroTurma = '';
    this.filtroOrigem = '';
    this.filtroTurno = '';
    this.filtroDR = null;
    this.filtroEscola = '';
    this.filtroCurso = '';
    this.filtroDocente = '';
    this.filtroAluno = '';
    this.filteredTurmas = [...this.turmas];
  }

  // -------------------------
  // Modal
  // -------------------------
  abrirModalNova(): void {
    this.isModalOpen = true;
    this.isEditing = false;
    this.currentTurmaId = null;
    this.resetForm();
  }

  abrirModalEditar(turma: any): void {
    this.isModalOpen = true;
    this.isEditing = true;
    this.currentTurmaId = turma.id;

    this.turmaForm = {
      name: turma.name || '',
      codigo: turma.codigo || '',
      origem: turma.origem || 'SIAC',
      turno: turma.turno || '',
      regional_department_id: turma.regional_department_id ||
        turma.operational_unit?.regional_department_id || null,
      operational_unit_id: turma.operational_unit_id || turma.operational_unit?.id || null,
      course_id: turma.course_id || turma.course?.id || null,
    };
  }

  fecharModal(): void {
    this.isModalOpen = false;
    this.resetForm();
  }

  resetForm(): void {
    this.turmaForm = {
      name: '',
      codigo: '',
      origem: 'SIAC',
      turno: '',
      regional_department_id: null,
      operational_unit_id: null,
      course_id: null,
    };
    this.errorMessage = null;
    this.fieldErrors = null;
    this.successMessage = null;
  }

  // -------------------------
  // CRUD
  // -------------------------
  private buildPayload() {
    const payload: any = { ...this.turmaForm };

    // se não informar código, usa o próprio nome
    if (!payload.codigo && payload.name) {
      payload.codigo = payload.name;
    }

    // se não informar DR, tenta deduzir pela UO
    if (!payload.regional_department_id && payload.operational_unit_id) {
      const uo = this.operationalUnits.find(u => u.id === payload.operational_unit_id);
      if (uo?.regional_department_id) {
        payload.regional_department_id = uo.regional_department_id;
      }
    }

    if (!payload.origem) {
      payload.origem = 'SIAC';
    }

    return payload;
  }

  salvarTurma(): void {
    if (this.isEditing) {
      this.atualizarTurma();
    } else {
      this.criarTurma();
    }
  }

  criarTurma(): void {
    this.isLoading = true;
    this.errorMessage = null;
    this.fieldErrors = null;

    const payload = this.buildPayload();

    const sub = this.turmaService.createTurma(payload).subscribe({
      next: () => {
        this.isLoading = false;
        this.successMessage = 'Turma criada com sucesso!';
        setTimeout(() => {
          this.fecharModal();
          this.loadTurmas();
        }, 800);
      },
      error: (err) => {
        this.isLoading = false;
        this.errorMessage = err?.error?.message ?? 'Erro ao criar turma.';
        this.fieldErrors = err?.error?.errors ?? null;
      }
    });
    this.subs.push(sub);
  }

  atualizarTurma(): void {
    if (!this.currentTurmaId) return;

    this.isLoading = true;
    this.errorMessage = null;
    this.fieldErrors = null;

    const payload = this.buildPayload();

    const sub = this.turmaService.updateTurma(this.currentTurmaId, payload).subscribe({
      next: () => {
        this.isLoading = false;
        this.successMessage = 'Turma atualizada com sucesso!';
        setTimeout(() => {
          this.fecharModal();
          this.loadTurmas();
        }, 800);
      },
      error: (err) => {
        this.isLoading = false;
        this.errorMessage = err?.error?.message ?? 'Erro ao atualizar turma.';
        this.fieldErrors = err?.error?.errors ?? null;
      }
    });
    this.subs.push(sub);
  }

  excluirTurma(id: number): void {
    if (!confirm('Tem certeza que deseja excluir esta turma?')) return;

    const sub = this.turmaService.deleteTurma(id).subscribe({
      next: () => {
        this.loadTurmas();
        alert('Turma excluída com sucesso!');
      },
      error: (err) => {
        console.error('Erro ao excluir turma:', err);
        alert('Erro ao excluir turma.');
      }
    });
    this.subs.push(sub);
  }

  // -------------------------
  // Helpers / view
  // -------------------------
  getOrigemNome(origem: string | null | undefined): string {
    if (!origem) return '—';
    return origem;
  }

  getRegionalNome(id: number | null): string {
    if (!id) return '—';
    const regional = this.regionalDepartments.find(r => r.id === id);
    return regional ? regional.name : '—';
  }

  getTurnoNome(turno: string): string {
    const t = this.turnos.find(x => x.id === turno);
    return t ? t.name : turno || '—';
  }

  countAlunos(turma: any): number {
    if (Array.isArray(turma.students)) {
      return turma.students.length;
    }
    if (typeof turma.quantidade_alunos === 'number') {
      return turma.quantidade_alunos;
    }
    return 0;
  }

  getDocentesResponsaveis(turma: any): string {
    if (Array.isArray(turma.teachers) && turma.teachers.length > 0) {
      return turma.teachers.map((t: any) => t.name).join(', ');
    }
    return turma.docente_responsavel || '—';
  }

  verDetalhes(turma: any): void {
    if (!turma?.id) return;
    this.router.navigate(['/app/turmas', turma.id]);
  }
}
