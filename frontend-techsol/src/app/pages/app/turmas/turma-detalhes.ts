// src/app/pages/app/turmas/turma-detalhes.ts
import { Component, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { TurmaService } from '../../../services/turma';
import { FormsModule } from '@angular/forms';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-turma-detalhes',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: 'turma-detalhes.html',
})
export class TurmaDetalhes implements OnInit, OnDestroy {
  turma: any = null;
  isLoading = false;
  errorMessage: string | null = null;
  successMessage: string | null = null;

  selectedTab: 'info' | 'professores' | 'alunos' = 'info';

  // Usuários do sistema
  allUsers: any[] = [];
  availableTeachers: any[] = [];
  availableStudents: any[] = [];

  // Pesquisa e seleção
  searchProfessor = '';
  searchAluno = '';
  selectedTeacherIds: number[] = [];
  selectedStudentIds: number[] = [];

  private subs: Subscription[] = [];

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private turmaService: TurmaService,
  ) {}

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    if (!id) {
      this.errorMessage = 'Turma não encontrada.';
      return;
    }
    this.loadTurma(id);
    this.loadUsers();
  }

  ngOnDestroy(): void {
    this.subs.forEach(s => s.unsubscribe());
  }

  // ---------------
  // CARREGAR DADOS
  // ---------------
  loadTurma(id: number): void {
    this.isLoading = true;
    this.errorMessage = null;

    const sub = this.turmaService.getTurma(id).subscribe({
      next: (resp) => {
        this.turma = resp;
        this.isLoading = false;
        this.atualizarListasDisponiveis();
      },
      error: (err) => {
        console.error(err);
        this.errorMessage = 'Erro ao carregar detalhes da turma.';
        this.isLoading = false;
      }
    });
    this.subs.push(sub);
  }

  loadUsers(): void {
    const sub = this.turmaService.getUsers().subscribe({
      next: (resp) => {
        const data = resp?.data ?? resp;
        if (Array.isArray(data)) {
          this.allUsers = data;
        } else {
          this.allUsers = [];
        }
        this.atualizarListasDisponiveis();
      },
      error: (err) => {
        console.warn('Erro ao carregar usuários', err);
      }
    });
    this.subs.push(sub);
  }

  // Atualiza as listas de professores/alunos disponíveis para adicionar
  atualizarListasDisponiveis(): void {
    if (!this.turma || !Array.isArray(this.allUsers)) return;

    const teacherIdsNaTurma = new Set(
      (this.turma.teachers || []).map((u: any) => u.id)
    );
    const studentIdsNaTurma = new Set(
      (this.turma.students || []).map((u: any) => u.id)
    );

    this.availableTeachers = this.allUsers.filter((u: any) =>
      Array.isArray(u.roles) &&
      u.roles.some((r: any) => r.slug === 'teacher') &&
      !teacherIdsNaTurma.has(u.id)
    );

    this.availableStudents = this.allUsers.filter((u: any) =>
      Array.isArray(u.roles) &&
      u.roles.some((r: any) => r.slug === 'student') &&
      !studentIdsNaTurma.has(u.id)
    );
  }

  // ---------------
  // TABS
  // ---------------
  setTab(tab: 'info' | 'professores' | 'alunos'): void {
    this.selectedTab = tab;
    this.successMessage = null;
    this.errorMessage = null;
  }

  // ---------------
  // HELPERS VIEW
  // ---------------
  getTurnoNome(turno: string): string {
    const map: any = {
      manha: 'Manhã',
      tarde: 'Tarde',
      noite: 'Noite',
      integral: 'Integral'
    };
    return map[turno] || turno || '—';
  }

  countAlunos(): number {
    if (Array.isArray(this.turma?.students)) {
      return this.turma.students.length;
    }
    return this.turma?.quantidade_alunos ?? 0;
  }

  getDocentesResponsaveis(): string {
    if (Array.isArray(this.turma?.teachers) && this.turma.teachers.length > 0) {
      return this.turma.teachers.map((t: any) => t.name).join(', ');
    }
    return this.turma?.docente_responsavel || '—';
  }

  filteredAvailableTeachers(): any[] {
    const term = this.searchProfessor.trim().toLowerCase();
    return this.availableTeachers.filter((u: any) => {
      const alreadySelected = this.selectedTeacherIds.includes(u.id);
      const match = !term ||
        u.name?.toLowerCase().includes(term) ||
        u.email?.toLowerCase().includes(term);
      return match && !alreadySelected; // os selecionados aparecem marcados no check
    });
  }

  filteredAvailableStudents(): any[] {
    const term = this.searchAluno.trim().toLowerCase();
    return this.availableStudents.filter((u: any) => {
      const alreadySelected = this.selectedStudentIds.includes(u.id);
      const match = !term ||
        u.name?.toLowerCase().includes(term) ||
        u.email?.toLowerCase().includes(term);
      return match && !alreadySelected;
    });
  }

  isTeacherSelected(id: number): boolean {
    return this.selectedTeacherIds.includes(id);
  }

  toggleTeacherSelection(id: number): void {
    if (this.selectedTeacherIds.includes(id)) {
      this.selectedTeacherIds = this.selectedTeacherIds.filter(x => x !== id);
    } else {
      this.selectedTeacherIds.push(id);
    }
  }

  isStudentSelected(id: number): boolean {
    return this.selectedStudentIds.includes(id);
  }

  toggleStudentSelection(id: number): void {
    if (this.selectedStudentIds.includes(id)) {
      this.selectedStudentIds = this.selectedStudentIds.filter(x => x !== id);
    } else {
      this.selectedStudentIds.push(id);
    }
  }

  // ---------------
  // AÇÕES PROFESSORES
  // ---------------
  adicionarProfessores(): void {
    if (!this.turma?.id || this.selectedTeacherIds.length === 0) return;

    const sub = this.turmaService.addTeachers(this.turma.id, this.selectedTeacherIds).subscribe({
      next: (resp) => {
        this.successMessage = 'Professores adicionados com sucesso!';
        this.selectedTeacherIds = [];
        // Atualiza turma com lista de teachers vinda da API
        this.turma.teachers = resp.teachers ?? this.turma.teachers;
        this.atualizarListasDisponiveis();
      },
      error: (err) => {
        console.error(err);
        this.errorMessage = err?.error?.message ?? 'Erro ao adicionar professores.';
      }
    });
    this.subs.push(sub);
  }

  removerProfessor(teacherId: number): void {
    if (!this.turma?.id) return;
    if (!confirm('Remover este professor da turma?')) return;

    const sub = this.turmaService.removeTeacher(this.turma.id, teacherId).subscribe({
      next: (resp) => {
        this.successMessage = 'Professor removido com sucesso!';
        this.turma.teachers = resp.teachers ?? this.turma.teachers;
        this.atualizarListasDisponiveis();
      },
      error: (err) => {
        console.error(err);
        this.errorMessage = err?.error?.message ?? 'Erro ao remover professor.';
      }
    });
    this.subs.push(sub);
  }

  // ---------------
  // AÇÕES ALUNOS
  // ---------------
  adicionarAlunos(): void {
    if (!this.turma?.id || this.selectedStudentIds.length === 0) return;

    const sub = this.turmaService.addStudents(this.turma.id, this.selectedStudentIds).subscribe({
      next: (resp) => {
        this.successMessage = 'Alunos adicionados com sucesso!';
        this.selectedStudentIds = [];
        this.turma.students = resp.students ?? this.turma.students;
        this.atualizarListasDisponiveis();
      },
      error: (err) => {
        console.error(err);
        this.errorMessage = err?.error?.message ?? 'Erro ao adicionar alunos.';
      }
    });
    this.subs.push(sub);
  }

  removerAluno(studentId: number): void {
    if (!this.turma?.id) return;
    if (!confirm('Remover este aluno da turma?')) return;

    const sub = this.turmaService.removeStudent(this.turma.id, studentId).subscribe({
      next: (resp) => {
        this.successMessage = 'Aluno removido com sucesso!';
        this.turma.students = resp.students ?? this.turma.students;
        this.atualizarListasDisponiveis();
      },
      error: (err) => {
        console.error(err);
        this.errorMessage = err?.error?.message ?? 'Erro ao remover aluno.';
      }
    });
    this.subs.push(sub);
  }

  // ---------------
  // NAVEGAÇÃO
  // ---------------
  voltar(): void {
    this.router.navigate(['/app/turmas']);
  }
}
