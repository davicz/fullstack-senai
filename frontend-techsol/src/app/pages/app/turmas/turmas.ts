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
  filtroOrigem: number | null = null;
  filtroTurno = '';
  filtroDR: number | null = null;
  filtroEscola = '';
  filtroCurso = '';
  filtroDocente = '';
  filtroAluno = '';

  // Listas para dropdowns
  origens: Array<{ id: number; name: string }> = [];
  turnos: Array<{ id: string; name: string }> = [
    { id: 'manha', name: 'Manhã' },
    { id: 'tarde', name: 'Tarde' },
    { id: 'noite', name: 'Noite' },
    { id: 'integral', name: 'Integral' }
  ];
  regionalDepartments: Array<{ id: number; name: string }> = [];

  // Modal
  isModalOpen = false;
  isEditing = false;
  currentTurmaId: number | null = null;

  // Form data
  turmaForm = {
    codigo: '',
    origem_id: null as number | null,
    turno: '',
    regional_department_id: null as number | null,
    operational_unit_id: null as number | null,
    curso_id: null as number | null,
    docente_responsavel: '',
    quantidade_alunos: 0
  };

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
        }
      },
      error: (err) => console.warn('Erro ao carregar origens', err)
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

  // -------------------------
  // Filtros
  // -------------------------
  aplicarFiltros(): void {
    let resultado = [...this.turmas];

    if (this.filtroTurma.trim()) {
      const termo = this.filtroTurma.toLowerCase();
      resultado = resultado.filter(t => 
        t.codigo?.toLowerCase().includes(termo)
      );
    }

    if (this.filtroOrigem) {
      resultado = resultado.filter(t => t.origem_id === this.filtroOrigem);
    }

    if (this.filtroTurno) {
      resultado = resultado.filter(t => t.turno === this.filtroTurno);
    }

    if (this.filtroDR) {
      resultado = resultado.filter(t => t.regional_department_id === this.filtroDR);
    }

    if (this.filtroEscola.trim()) {
      const termo = this.filtroEscola.toLowerCase();
      resultado = resultado.filter(t => 
        t.escola?.toLowerCase().includes(termo)
      );
    }

    if (this.filtroCurso.trim()) {
      const termo = this.filtroCurso.toLowerCase();
      resultado = resultado.filter(t => 
        t.curso?.toLowerCase().includes(termo)
      );
    }

    if (this.filtroDocente.trim()) {
      const termo = this.filtroDocente.toLowerCase();
      resultado = resultado.filter(t => 
        t.docente_responsavel?.toLowerCase().includes(termo)
      );
    }

    if (this.filtroAluno.trim()) {
      const termo = this.filtroAluno.toLowerCase();
      resultado = resultado.filter(t => 
        t.quantidade_alunos?.toString().includes(termo)
      );
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
    this.resetForm();
  }

  abrirModalEditar(turma: any): void {
    this.isModalOpen = true;
    this.isEditing = true;
    this.currentTurmaId = turma.id;
    
    this.turmaForm = {
      codigo: turma.codigo || '',
      origem_id: turma.origem_id || null,
      turno: turma.turno || '',
      regional_department_id: turma.regional_department_id || null,
      operational_unit_id: turma.operational_unit_id || null,
      curso_id: turma.curso_id || null,
      docente_responsavel: turma.docente_responsavel || '',
      quantidade_alunos: turma.quantidade_alunos || 0
    };
  }

  fecharModal(): void {
    this.isModalOpen = false;
    this.resetForm();
  }

  resetForm(): void {
    this.turmaForm = {
      codigo: '',
      origem_id: null,
      turno: '',
      regional_department_id: null,
      operational_unit_id: null,
      curso_id: null,
      docente_responsavel: '',
      quantidade_alunos: 0
    };
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

  criarTurma(): void {
    this.isLoading = true;
    this.errorMessage = null;
    this.fieldErrors = null;

    const sub = this.turmaService.createTurma(this.turmaForm).subscribe({
      next: () => {
        this.isLoading = false;
        this.successMessage = 'Turma criada com sucesso!';
        setTimeout(() => {
          this.fecharModal();
          this.loadTurmas();
        }, 1000);
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

    const sub = this.turmaService.updateTurma(this.currentTurmaId, this.turmaForm).subscribe({
      next: () => {
        this.isLoading = false;
        this.successMessage = 'Turma atualizada com sucesso!';
        setTimeout(() => {
          this.fecharModal();
          this.loadTurmas();
        }, 1000);
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
  // Helpers
  // -------------------------
  getOrigemNome(id: number | null): string {
    if (!id) return '—';
    const origem = this.origens.find(o => o.id === id);
    return origem ? origem.name : '—';
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
}