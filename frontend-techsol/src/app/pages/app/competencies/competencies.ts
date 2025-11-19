// src/app/pages/app/competencies/competencies.ts
import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CompetencyService } from '../../../services/competency';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-competencies',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './competencies.html',
  styleUrl: './competencies.css',
})
export class Competencies implements OnInit, OnDestroy {
  // Lista de competências
  competencies: any[] = [];
  filteredCompetencies: any[] = [];
  isListLoading = false;
  listError: string | null = null;

  // Filtros
  searchTerm = '';
  filterLevel: number | null = null;
  filterRootOnly = false;

  // Modal
  isModalOpen = false;
  isEditing = false;
  currentCompetencyId: number | null = null;

  // Form data
  competencyForm = {
    code: '',
    name: '',
    description: '',
    parent_id: null as number | null,
    level: 1,
    is_active: true
  };

  // Níveis disponíveis
  levels = [
    { value: 1, label: 'Básico' },
    { value: 2, label: 'Intermediário' },
    { value: 3, label: 'Avançado' },
    { value: 4, label: 'Especialista' },
    { value: 5, label: 'Expert' }
  ];

  // Feedback
  isLoading = false;
  errorMessage: string | null = null;
  fieldErrors: any = null;
  successMessage: string | null = null;

  // Subscriptions
  private subs: Subscription[] = [];

  constructor(private competencyService: CompetencyService) {}

  ngOnInit(): void {
    this.loadCompetencies();
  }

  ngOnDestroy(): void {
    this.subs.forEach(s => s.unsubscribe());
  }

  // -------------------------
  // Carregar dados
  // -------------------------
  loadCompetencies(): void {
    this.isListLoading = true;
    this.listError = null;

    const filters: any = {
      active_only: true
    };

    if (this.filterRootOnly) {
      filters.root_only = true;
    }

    if (this.filterLevel) {
      filters.level = this.filterLevel;
    }

    const sub = this.competencyService.getCompetencies(filters).subscribe({
      next: (resp) => {
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.competencies = data;
        } else {
          console.warn('Resposta de competências inesperada', resp);
          this.competencies = [];
        }
        this.applyFilters();
        this.isListLoading = false;
      },
      error: (err) => {
        console.error('Erro ao carregar competências:', err);
        this.listError = 'Falha ao carregar competências.';
        this.isListLoading = false;
      }
    });
    this.subs.push(sub);
  }

  // -------------------------
  // Filtros
  // -------------------------
  applyFilters(): void {
    let resultado = [...this.competencies];

    if (this.searchTerm.trim()) {
      const termo = this.searchTerm.toLowerCase();
      resultado = resultado.filter(c => 
        c.code?.toLowerCase().includes(termo) ||
        c.name?.toLowerCase().includes(termo) ||
        c.description?.toLowerCase().includes(termo)
      );
    }

    this.filteredCompetencies = resultado;
  }

  clearFilters(): void {
    this.searchTerm = '';
    this.filterLevel = null;
    this.filterRootOnly = false;
    this.loadCompetencies();
  }

  // -------------------------
  // Modal
  // -------------------------
  openModalNew(): void {
    this.isModalOpen = true;
    this.isEditing = false;
    this.currentCompetencyId = null;
    this.resetForm();
  }

  openModalEdit(competency: any): void {
    this.isModalOpen = true;
    this.isEditing = true;
    this.currentCompetencyId = competency.id;
    
    this.competencyForm = {
      code: competency.code || '',
      name: competency.name || '',
      description: competency.description || '',
      parent_id: competency.parent_id || null,
      level: competency.level || 1,
      is_active: competency.is_active ?? true
    };
  }

  closeModal(): void {
    this.isModalOpen = false;
    this.resetForm();
  }

  resetForm(): void {
    this.competencyForm = {
      code: '',
      name: '',
      description: '',
      parent_id: null,
      level: 1,
      is_active: true
    };
    this.errorMessage = null;
    this.fieldErrors = null;
    this.successMessage = null;
  }

  // -------------------------
  // CRUD
  // -------------------------
  saveCompetency(): void {
    if (this.isEditing) {
      this.updateCompetency();
    } else {
      this.createCompetency();
    }
  }

  createCompetency(): void {
    this.isLoading = true;
    this.errorMessage = null;
    this.fieldErrors = null;

    const sub = this.competencyService.createCompetency(this.competencyForm).subscribe({
      next: () => {
        this.isLoading = false;
        this.successMessage = 'Competência criada com sucesso!';
        setTimeout(() => {
          this.closeModal();
          this.loadCompetencies();
        }, 1000);
      },
      error: (err) => {
        this.isLoading = false;
        this.errorMessage = err?.error?.message ?? 'Erro ao criar competência.';
        this.fieldErrors = err?.error?.errors ?? null;
      }
    });
    this.subs.push(sub);
  }

  updateCompetency(): void {
    if (!this.currentCompetencyId) return;

    this.isLoading = true;
    this.errorMessage = null;
    this.fieldErrors = null;

    const sub = this.competencyService.updateCompetency(this.currentCompetencyId, this.competencyForm).subscribe({
      next: () => {
        this.isLoading = false;
        this.successMessage = 'Competência atualizada com sucesso!';
        setTimeout(() => {
          this.closeModal();
          this.loadCompetencies();
        }, 1000);
      },
      error: (err) => {
        this.isLoading = false;
        this.errorMessage = err?.error?.message ?? 'Erro ao atualizar competência.';
        this.fieldErrors = err?.error?.errors ?? null;
      }
    });
    this.subs.push(sub);
  }

  deleteCompetency(id: number): void {
    if (!confirm('Tem certeza que deseja excluir esta competência? Isso pode afetar avaliações existentes.')) return;

    const sub = this.competencyService.deleteCompetency(id).subscribe({
      next: () => {
        this.loadCompetencies();
        alert('Competência excluída com sucesso!');
      },
      error: (err) => {
        console.error('Erro ao excluir competência:', err);
        alert('Erro ao excluir competência. Verifique se não há avaliações vinculadas.');
      }
    });
    this.subs.push(sub);
  }

  // -------------------------
  // Helpers
  // -------------------------
  getLevelLabel(level: number): string {
    const l = this.levels.find(x => x.value === level);
    return l ? l.label : `Nível ${level}`;
  }

  getParentName(parentId: number | null): string {
    if (!parentId) return '—';
    const parent = this.competencies.find(c => c.id === parentId);
    return parent ? parent.name : '—';
  }

  hasChildren(competencyId: number): boolean {
    return this.competencies.some(c => c.parent_id === competencyId);
  }
}