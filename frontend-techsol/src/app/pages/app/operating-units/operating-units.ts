import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { OperatingUnitsService } from '../../../services/operating-units';

// Interfaces baseadas no seu JSON real + campos que o Frontend espera
export interface RegionalDepartment {
  id: number;
  name: string;
  acronym?: string; // O JSON atual não trouxe, mas deixamos opcional
}

export interface OperatingUnitInterface {
  id?: number;
  regional_department_id: number | null;
  regional_department?: RegionalDepartment; // O objeto aninhado que vem da API
  
  name: string;
  
  // Campos que existem na tela mas ainda não vieram no seu JSON (deixei opcionais)
  code?: string;
  city?: string;
  days_submission?: number;
  approval_percentage?: number;
  
  created_at?: string;
  updated_at?: string;
}

@Component({
  selector: 'app-operating-units',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './operating-units.html',
  styleUrls: ['./operating-units.css']
})
export class OperatingUnits implements OnInit {

  // --- ESTADOS DE TELA ---
  isLoading: boolean = false;
  isModalOpen: boolean = false;
  isEditing: boolean = false;
  errorMessage: string = '';

  // --- DADOS ---
  regionalDepartments: RegionalDepartment[] = [];
  units: OperatingUnitInterface[] = [];
  filteredUnits: OperatingUnitInterface[] = [];

  // --- FILTROS ---
  filtroDR: number | null = null;
  filtroCidade: string = '';
  filtroNome: string = '';
  filtroEnvio: number | null = null;
  filtroAprovacao: number | null = null;

  // --- FORMULÁRIO ---
  unitForm: OperatingUnitInterface = {
    regional_department_id: null,
    name: '',
    code: '',
    city: '',
    days_submission: 0,
    approval_percentage: 0
  };

  constructor(private service: OperatingUnitsService) { }

  ngOnInit(): void {
    this.loadRegionalDepartments();
    this.loadUnits();
  }

  // 1. Carrega os DRs para o <select> do modal
  loadRegionalDepartments() {
    this.service.getRegionalDepartments().subscribe({
      next: (data) => {
        this.regionalDepartments = data;
      },
      error: (err) => console.error('Erro ao carregar DRs', err)
    });
  }

  // 2. Carrega as Unidades da API
  loadUnits() {
    this.isLoading = true;
    this.service.getOperatingUnits().subscribe({
      next: (data) => {
        this.units = data;
        this.aplicarFiltros(); // Aplica filtros iniciais (exibe tudo)
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erro ao buscar unidades:', error);
        this.errorMessage = 'Erro ao carregar dados da API.';
        this.isLoading = false;
      }
    });
  }

  // --- LÓGICA DE FILTROS (Mantida igual, roda no Frontend) ---
  aplicarFiltros() {
    this.filteredUnits = this.units.filter(unit => {
      const matchDR = this.filtroDR ? unit.regional_department_id === this.filtroDR : true;
      
      // Tratamento seguro para strings (caso venha null da API)
      const city = unit.city || '';
      const name = unit.name || '';

      const matchCidade = this.filtroCidade 
        ? city.toLowerCase().includes(this.filtroCidade.toLowerCase()) 
        : true;
      
      const matchNome = this.filtroNome 
        ? name.toLowerCase().includes(this.filtroNome.toLowerCase()) 
        : true;
      
      const matchEnvio = this.filtroEnvio 
        ? unit.days_submission === this.filtroEnvio 
        : true;

      const matchAprovacao = this.filtroAprovacao 
        ? (unit.approval_percentage || 0) >= this.filtroAprovacao 
        : true;

      return matchDR && matchCidade && matchNome && matchEnvio && matchAprovacao;
    });
  }

  limparFiltros() {
    this.filtroDR = null;
    this.filtroCidade = '';
    this.filtroNome = '';
    this.filtroEnvio = null;
    this.filtroAprovacao = null;
    this.aplicarFiltros();
  }

  // --- MODAL & OPERAÇÕES CRUD ---
  abrirModalNova() {
    this.isEditing = false;
    this.unitForm = {
      regional_department_id: null,
      name: '',
      code: '',
      city: '',
      days_submission: 0,
      approval_percentage: 0
    };
    this.isModalOpen = true;
  }

  abrirModalEditar(unit: OperatingUnitInterface) {
    this.isEditing = true;
    // Clona o objeto para não editar a tabela visualmente antes de salvar
    this.unitForm = { ...unit }; 
    this.isModalOpen = true;
  }

  fecharModal() {
    this.isModalOpen = false;
    this.errorMessage = '';
  }

  salvarUnit() {
    if (!this.unitForm.name || !this.unitForm.regional_department_id) {
      alert('Por favor, preencha o Nome e o DR.');
      return;
    }

    this.isLoading = true;

    if (this.isEditing && this.unitForm.id) {
      // --- ATUALIZAR (PUT) ---
      this.service.updateOperatingUnit(this.unitForm.id, this.unitForm).subscribe({
        next: (response) => {
          alert('Escola atualizada com sucesso!');
          this.loadUnits(); // Recarrega a lista para pegar dados frescos
          this.fecharModal();
        },
        error: (err) => {
          console.error(err);
          alert('Erro ao atualizar escola.');
          this.isLoading = false;
        }
      });
    } else {
      // --- CRIAR (POST) ---
      this.service.createOperatingUnit(this.unitForm).subscribe({
        next: (response) => {
          alert('Escola cadastrada com sucesso!');
          this.loadUnits();
          this.fecharModal();
        },
        error: (err) => {
          console.error(err);
          alert('Erro ao cadastrar escola. Verifique se o DR é válido.');
          this.isLoading = false;
        }
      });
    }
  }
  
  getDrAcronym(drName: string | undefined): string {
    if (!drName) return '—';
    
    // Lógica simples: Procura o nome do estado e retorna a sigla
    const name = drName.toLowerCase();
    
    if (name.includes('alagoas')) return 'AL';
    if (name.includes('são paulo') || name.includes('sao paulo')) return 'SP';
    if (name.includes('rio de janeiro')) return 'RJ';
    if (name.includes('minas gerais')) return 'MG';
    if (name.includes('bahia')) return 'BA';
    if (name.includes('ceará') || name.includes('ceara')) return 'CE';
    // Adicione outros estados conforme sua necessidade...

    // Se não encontrar nenhum conhecido, retorna o nome original (Ex: "DR Alagoas")
    return drName; 
  }
}