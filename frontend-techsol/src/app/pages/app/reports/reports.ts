// src/app/pages/app/reports/reports.ts
import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ReportService } from '../../../services/report';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-reports',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './reports.html',
  styleUrl: './reports.css',
})
export class Reports implements OnInit, OnDestroy {
  // Referência ao Math para usar no template
  Math = Math;

  // Aba ativa
  activeTab: 'item' | 'curso' | 'area' = 'item';

  // Lista de itens (questões)
  items: any[] = [];
  isLoading = false;
  error: string | null = null;

  // Paginação
  currentPage = 1;
  perPage = 50;
  totalItems = 0;
  totalPages = 0;

  // Filtros
  filters = {
    itinerario: '',
    identificador: '',
    curso: '',
    subfunction: '',
    performance_standard: '',
    capacity: '',
    difficulty: '',
    respostas_min: '',
    respostas_max: '',
    acertos_min: '',
    acertos_max: '',
    gabarito: '',
    alternativa: ''
  };

  // Listas para dropdowns
  availableYears: number[] = [];
  difficulties = [
    { value: 'muito_facil', label: 'Muito Fácil' },
    { value: 'facil', label: 'Fácil' },
    { value: 'medio', label: 'Médio' },
    { value: 'dificil', label: 'Difícil' },
    { value: 'muito_dificil', label: 'Muito Difícil' }
  ];

  alternativas = ['A', 'B', 'C', 'D', 'E'];

  // Modal de detalhes
  isModalOpen = false;
  selectedQuestion: any = null;
  isLoadingDetails = false;

  // Ordenação
  sortBy = 'created_at';
  sortOrder: 'asc' | 'desc' = 'desc';

  private subs: Subscription[] = [];

  constructor(public reportService: ReportService) {}

  ngOnInit(): void {
    this.availableYears = this.reportService.getAvailableYears();
    this.loadItems();
  }

  ngOnDestroy(): void {
    this.subs.forEach(s => s.unsubscribe());
  }

  // -------------------------
  // Tabs
  // -------------------------
  switchTab(tab: 'item' | 'curso' | 'area'): void {
    this.activeTab = tab;
    if (tab === 'item') {
      this.loadItems();
    }
  }

  // -------------------------
  // Carregar dados
  // -------------------------
  loadItems(): void {
    this.isLoading = true;
    this.error = null;

    const params: any = {
      page: this.currentPage,
      per_page: this.perPage,
      sort_by: this.sortBy,
      sort_order: this.sortOrder
    };

    // Adicionar filtros não vazios
    Object.keys(this.filters).forEach(key => {
      const value = (this.filters as any)[key];
      if (value && value.trim && value.trim()) {
        params[key] = value.trim();
      } else if (value && !value.trim) {
        params[key] = value;
      }
    });

    const sub = this.reportService.getPerformanceByItem(params).subscribe({
      next: (response) => {
        this.items = response.data || [];
        this.totalItems = response.meta?.total || 0;
        this.totalPages = response.meta?.last_page || 1;
        this.currentPage = response.meta?.current_page || 1;
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Erro ao carregar relatório:', err);
        this.error = 'Erro ao carregar relatório. Tente novamente.';
        this.isLoading = false;
      }
    });

    this.subs.push(sub);
  }

  // -------------------------
  // Filtros
  // -------------------------
  applyFilters(): void {
    this.currentPage = 1;
    this.loadItems();
  }

  clearFilters(): void {
    this.filters = {
      itinerario: '',
      identificador: '',
      curso: '',
      subfunction: '',
      performance_standard: '',
      capacity: '',
      difficulty: '',
      respostas_min: '',
      respostas_max: '',
      acertos_min: '',
      acertos_max: '',
      gabarito: '',
      alternativa: ''
    };
    this.currentPage = 1;
    this.loadItems();
  }

  // -------------------------
  // Ordenação
  // -------------------------
  sort(column: string): void {
    if (this.sortBy === column) {
      this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortBy = column;
      this.sortOrder = 'desc';
    }
    this.loadItems();
  }

  getSortIcon(column: string): string {
    if (this.sortBy !== column) return '↕';
    return this.sortOrder === 'asc' ? '↑' : '↓';
  }

  // -------------------------
  // Paginação
  // -------------------------
  goToPage(page: number): void {
    if (page >= 1 && page <= this.totalPages) {
      this.currentPage = page;
      this.loadItems();
    }
  }

  nextPage(): void {
    if (this.currentPage < this.totalPages) {
      this.currentPage++;
      this.loadItems();
    }
  }

  previousPage(): void {
    if (this.currentPage > 1) {
      this.currentPage--;
      this.loadItems();
    }
  }

  // -------------------------
  // Modal de Detalhes
  // -------------------------
  openDetails(item: any): void {
    this.isModalOpen = true;
    this.isLoadingDetails = true;
    this.selectedQuestion = null;

    const sub = this.reportService.getQuestionDetails(item.id).subscribe({
      next: (response) => {
        this.selectedQuestion = response;
        this.isLoadingDetails = false;
      },
      error: (err) => {
        console.error('Erro ao carregar detalhes:', err);
        this.isLoadingDetails = false;
        alert('Erro ao carregar detalhes da questão.');
        this.closeModal();
      }
    });

    this.subs.push(sub);
  }

  closeModal(): void {
    this.isModalOpen = false;
    this.selectedQuestion = null;
  }

  // -------------------------
  // Helpers
  // -------------------------
  getAccuracyColorClass(rate: number): string {
    return this.reportService.getAccuracyColor(rate);
  }

  getDifficultyBadgeClass(difficulty: string): string {
    return this.reportService.getDifficultyColor(difficulty);
  }

  formatPercentage(value: number): string {
    if (value === null || value === undefined) return '0.00%';
    return value.toFixed(2) + '%';
  }
}