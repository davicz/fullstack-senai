// src/app/pages/app/turmas/turmas.ts
import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { TurmaService } from '../../../services/turma';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-turmas',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './turmas.html',
  styleUrl: './turmas.css',
})
export class Turmas implements OnInit, OnDestroy {
  // Lista de turmas
  turmas: any[] = [];
  filteredTurmas: any[] = [];
  isListLoading = false;
  listError: string | null = null;

  // Filtros
  filtroTurma = '';
  filtroOrigem: string | null = null;
  filtroTurno = '';
  filtroDR: number | null = null;
  filtroEscola = '';
  filtroCurso = '';
  filtroDocente = '';
  filtroAluno = '';

  // Listas auxiliares
  origens: Array<{ id: any; name: string }> = [];
  turnos: Array<{ id: string; name: string }> = [
    { id: 'manha', name: 'Manhã' },
    { id: 'tarde', name: 'Tarde' },
    { id: 'noite', name: 'Noite' },
    { id: 'integral', name: 'Integral' }
  ];
  regionalDepartments: Array<{ id: number; name: string }> = [];
  operationalUnits: Array<{ id: number; name: string; regional_department_id?: number | null }> = [];
  cursos: Array<{ id: number; name: string }> = [];

  // Modal
  isModalOpen = false;
  isEditing = false;
  currentTurmaId: number | null = null;

  // Form (espelhando o backend)
  turmaForm: {
    name: string;
    codigo: string;
    origem: string;
    turno: string;
    course_id: number | null;
    operational_unit_id: number | null;
    regional_department_id: number | null;
    docente_responsavel: string;
    quantidade_alunos: number;
  } = this.createEmptyForm();

  // Feedback
  isLoading = false;
  errorMessage: string | null = null;
  fieldErrors: any = null;
  successMessage: string | null = null;

  // Subscriptions
  private subs: Subscription[] = [];

  constructor(private turmaService: TurmaService) {}

  ngOnInit(): void {
    this.loadTurmas();
    this.loadOrigens();
    this.loadRegionalDepartments();
    this.loadOperationalUnits();
    this.loadCursos();
  }

  ngOnDestroy(): void {
    this.subs.forEach(s => s.unsubscribe());
  }

  // -------------------------
  // Helpers de form
  // -------------------------
  private createEmptyForm() {
    return {
      name: '',
      codigo: '',
      origem: 'SIAC',
      turno: '',
      course_id: null,
      operational_unit_id: null,
      regional_department_id: null,
      docente_responsavel: '',
      quantidade_alunos: 0
    };
  }

  // -------------------------
  // Carregar dados
  // -------------------------
  loadTurmas(): void {
    this.isListLoading = true;
    this.listError = null;

    const sub = this.turmaService.getTurmas().subscribe({
      next: (resp) => {
        const data = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : []);
        if (!Array.isArray(data)) {
          console.warn('Resposta de turmas inesperada', resp);
          this.turmas = [];
        } else {
          this.turmas = data;
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
        const data = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : []);
        if (Array.isArray(data)) {
          this.origens = data;
        }
      },
      error: (err) => console.warn('Erro ao carregar origens', err)
    });
    this.subs.push(sub);
  }

  loadRegionalDepartments(): void {
    const sub = this.turmaService.getRegionalDepartments().subscribe({
      next: (resp) => {
        const data = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : []);
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
        const data = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : []);
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
        const data = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : []);
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

    // Turma (nome/código)
    if (this.filtroTurma.trim()) {
      const termo = this.filtroTurma.toLowerCase();
      resultado = resultado.filter(t =>
        (t.name || '').toLowerCase().includes(termo) ||
        (t.codigo || '').toLowerCase().includes(termo)
      );
    }

    // Origem
    if (this.filtroOrigem) {
      const termo = this.filtroOrigem.toLowerCase();
      resultado = resultado.filter(t =>
        (t.origem || '').toLowerCase() === termo
      );
    }

    // Turno
    if (this.filtroTurno) {
      resultado = resultado.filter(t => (t.turno || '') === this.filtroTurno);
    }

    // DR
    if (this.filtroDR) {
      resultado = resultado.filter(t => {
        const drId = t.regional_department_id ?? t.operational_unit?.regional_department_id ?? null;
        return drId === this.filtroDR;
      });
    }

    // Escola
    if (this.filtroEscola.trim()) {
      const termo = this.filtroEscola.toLowerCase();
      resultado = resultado.filter(t =>
        (t.operational_unit?.name || '').toLowerCase().includes(termo)
      );
    }

    // Curso
    if (this.filtroCurso.trim()) {
      const termo = this.filtroCurso.toLowerCase();
      resultado = resultado.filter(t =>
        (t.course?.name || '').toLowerCase().includes(termo)
      );
    }

    // Docente
    if (this.filtroDocente.trim()) {
      const termo = this.filtroDocente.toLowerCase();
      resultado = resultado.filter(t =>
        this.getTeacherNames(t).toLowerCase().includes(termo)
      );
    }

    // Alunos (quantidade)
    if (this.filtroAluno.trim()) {
      const termo = this.filtroAluno.toLowerCase();
      resultado = resultado.filter(t => {
        const qtd = this.getStudentsCount(t);
        return qtd.toString().includes(termo);
      });
    }

    this.filteredTurmas = resultado;
  }

  limparFiltros(): void {
    this.filtroTurma = '';
    this.filtroOrigem = null;
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
    this.turmaForm = this.createEmptyForm();
    this.errorMessage = null;
    this.fieldErrors = null;
    this.successMessage = null;
  }

  abrirModalEditar(turma: any): void {
    this.isModalOpen = true;
    this.isEditing = true;
    this.currentTurmaId = turma.id;

    this.turmaForm = {
      name: turma.name || '',
      codigo: turma.codigo || turma.name || '',
      origem: turma.origem || 'SIAC',
      turno: turma.turno || '',
      course_id: turma.course_id || (turma.course?.id ?? null),
      operational_unit_id: turma.operational_unit_id || (turma.operational_unit?.id ?? null),
      regional_department_id:
        turma.regional_department_id ?? turma.operational_unit?.regional_department_id ?? null,
      docente_responsavel: turma.docente_responsavel || '',
      quantidade_alunos: turma.quantidade_alunos ?? this.getStudentsCount(turma),
    };

    this.errorMessage = null;
    this.fieldErrors = null;
    this.successMessage = null;
  }

  fecharModal(): void {
    this.isModalOpen = false;
    this.turmaForm = this.createEmptyForm();
    this.errorMessage = null;
    this.fieldErrors = null;
    this.successMessage = null;
  }

  // -------------------------
  // CRUD
  // -------------------------
  salvarTurma(): void {
    if (this.isEditing) {
      this.atualizarTurma();
    } else {
      this.criarTurma();
    }
  }

  private buildPayloadFromForm() {
    // DR derivado da UO caso não tenha vindo explícito
    const regionalFromUO = this.getRegionalFromUO(this.turmaForm.operational_unit_id);

    return {
      name: this.turmaForm.name,
      codigo: this.turmaForm.codigo || this.turmaForm.name,
      origem: this.turmaForm.origem || 'SIAC',
      turno: this.turmaForm.turno || 'manha',
      course_id: this.turmaForm.course_id,
      operational_unit_id: this.turmaForm.operational_unit_id,
      regional_department_id: this.turmaForm.regional_department_id || regionalFromUO,
      docente_responsavel: this.turmaForm.docente_responsavel || null,
      quantidade_alunos: this.turmaForm.quantidade_alunos ?? 0
    };
  }

  criarTurma(): void {
    this.isLoading = true;
    this.errorMessage = null;
    this.fieldErrors = null;

    const payload = this.buildPayloadFromForm();

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

    const payload = this.buildPayloadFromForm();

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
  // Helpers de UI
  // -------------------------
  getOrigemNome(origem: string | null | undefined): string {
    if (!origem) return '—';
    const found = this.origens.find(o => o.name.toLowerCase() === origem.toLowerCase());
    return found ? found.name : origem;
  }

  getRegionalNome(id: number | null): string {
    if (!id) return '—';
    const regional = this.regionalDepartments.find(r => r.id === id);
    return regional ? regional.name : '—';
  }

  getTurnoNome(turno: string): string {
    const t = this.turnos.find(x => x.id === turno);
    return t ? t.name : turno;
  }

  getTeacherNames(turma: any): string {
    if (Array.isArray(turma.teachers) && turma.teachers.length) {
      return turma.teachers.map((t: any) => t.name).join(', ');
    }
    return turma.docente_responsavel || '—';
  }

  getStudentsCount(turma: any): number {
    if (Array.isArray(turma.students)) {
      return turma.students.length;
    }
    return turma.quantidade_alunos ?? 0;
  }

  private getRegionalFromUO(uoId: number | null): number | null {
    if (!uoId) return null;
    const uo = this.operationalUnits.find(u => u.id === uoId);
    return uo?.regional_department_id ?? null;
  }
}
