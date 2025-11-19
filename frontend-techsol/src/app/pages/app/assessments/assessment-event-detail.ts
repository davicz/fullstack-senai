// src/app/pages/app/assessments/assessment-event-detail.ts
import { Component, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  AssessmentEvent,
  AssessmentEventStudent,
  AssessmentService,
} from '../../../services/assessment';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-assessment-event-detail',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './assessment-event-detail.html',
})
export class AssessmentEventDetail implements OnInit, OnDestroy {
  event: AssessmentEvent | null = null;

  // Abas
  activeTab: 'courses' | 'admins' | 'teachers' | 'scheduling' = 'scheduling';

  // Alunos
  students: AssessmentEventStudent[] = [];
  filteredStudents: AssessmentEventStudent[] = [];
  isStudentsLoading = false;
  studentsError: string | null = null;

  // Resumo
  get totalStudents(): number {
    return this.event?.total_students ?? 0;
  }
  get invitedCount(): number {
    return this.event?.invited_students ?? 0;
  }
  get scheduledCount(): number {
    return this.event?.scheduled_students ?? 0;
  }
  get completedCount(): number {
    return this.event?.completed_students ?? 0;
  }

  get scheduledPercent(): number {
    const t = this.totalStudents;
    if (!t) return 0;
    return Math.round((this.scheduledCount * 100) / t);
  }

  get completedPercent(): number {
    const t = this.totalStudents;
    if (!t) return 0;
    return Math.round((this.completedCount * 100) / t);
  }

  /**
   * Helpers para exibir o período de agendamento de provas.
   * Se o backend usar exam_start_date / exam_end_date,
   * também tentamos pegar esses campos.
   */
  get schedulingStartDisplay(): string {
    if (!this.event) return '—';
    const ev: any = this.event;
    const value = ev.scheduling_start_date ?? ev.exam_start_date ?? null;
    return this.formatDate(value);
  }

  get schedulingEndDisplay(): string {
    if (!this.event) return '—';
    const ev: any = this.event;
    const value = ev.scheduling_end_date ?? ev.exam_end_date ?? null;
    return this.formatDate(value);
  }

  // Filtros alunos
  searchStudent = '';
  filterStatus: string = 'all'; // all | not_scheduled | invited | scheduled | completed
  filterClass: string | null = null;
  availableClasses: string[] = [];

  // Estado geral
  isEventLoading = false;
  eventError: string | null = null;

  // Modal: adicionar alunos em lote
  isAddStudentsModalOpen = false;
  addStudentsMode: 'cpf' | 'csv' | 'select' = 'cpf';
  addStudentsText = '';
  addStudentsLoading = false;
  addStudentsError: string | null = null;
  addStudentsSuccess: string | null = null;

  // Modal: agendar aluno
  isScheduleModalOpen = false;
  scheduleStudentSelected: AssessmentEventStudent | null = null;
  scheduleDateTime: string = '';
  scheduleLoading = false;
  scheduleError: string | null = null;
  scheduleSuccess: string | null = null;

  private subs: Subscription[] = [];

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private assessmentService: AssessmentService
  ) {}

  ngOnInit(): void {
    const idParam = this.route.snapshot.paramMap.get('id');
    const id = idParam ? Number(idParam) : null;

    if (!id) {
      this.eventError = 'Evento não encontrado.';
      return;
    }

    this.loadEvent(id);
    this.loadStudents(id);
  }

  ngOnDestroy(): void {
    this.subs.forEach((s) => s.unsubscribe());
  }

  // ===================================
  // Carregamento
  // ===================================
  loadEvent(id: number): void {
    this.isEventLoading = true;
    this.eventError = null;

    const sub = this.assessmentService.getEvent(id).subscribe({
      next: (ev) => {
        this.event = ev;
        this.isEventLoading = false;
      },
      error: (err) => {
        console.error('Erro ao carregar evento:', err);
        this.eventError = 'Falha ao carregar dados do evento.';
        this.isEventLoading = false;
      },
    });

    this.subs.push(sub);
  }

  loadStudents(id: number): void {
    this.isStudentsLoading = true;
    this.studentsError = null;

    const sub = this.assessmentService.getEventStudents(id).subscribe({
      next: (resp: any) => {
        const data = resp?.data ?? resp;
        this.students = Array.isArray(data) ? data : [];
        this.buildAvailableClasses();
        this.applyStudentFilters();
        this.isStudentsLoading = false;
      },
      error: (err) => {
        console.error('Erro ao carregar alunos:', err);
        this.studentsError = 'Falha ao carregar alunos.';
        this.isStudentsLoading = false;
      },
    });

    this.subs.push(sub);
  }

  buildAvailableClasses(): void {
    const set = new Set<string>();
    this.students.forEach((s) => {
      if (s.school_class_name) set.add(s.school_class_name);
    });
    this.availableClasses = Array.from(set);
  }

  // ===================================
  // Tabs
  // ===================================
  setTab(tab: 'courses' | 'admins' | 'teachers' | 'scheduling'): void {
    this.activeTab = tab;
  }

  // ===================================
  // Helpers de status / datas
  // ===================================
  getStatusBadge(): { label: string; color: string } {
    if (!this.event) return { label: '-', color: 'bg-gray-200 text-gray-700' };

    switch (this.event.status) {
      case 'open':
        return { label: 'Aberto', color: 'bg-green-100 text-green-700' };
      case 'closed':
        return { label: 'Encerrado', color: 'bg-red-100 text-red-700' };
      case 'scheduled':
        return { label: 'Agendado', color: 'bg-blue-100 text-blue-700' };
      case 'draft':
        return { label: 'Rascunho', color: 'bg-gray-100 text-gray-700' };
      default:
        return { label: this.event.status, color: 'bg-gray-200 text-gray-700' };
    }
  }

  // Backend já manda as datas formatadas, então só garantimos que não seja nulo
  formatDate(dateStr: string | null | undefined): string {
    if (!dateStr) return '—';
    return dateStr;
  }

  formatDateTime(dateStr: string | null | undefined): string {
    if (!dateStr) return '—';
    return dateStr;
  }

  // ===================================
  // Filtros de alunos
  // ===================================
  applyStudentFilters(): void {
    let result = [...this.students];

    if (this.searchStudent.trim()) {
      const termRaw = this.searchStudent.trim().toLowerCase();
      const termCpf = termRaw.replace(/\D/g, '');

      result = result.filter((s) => {
        const name = (s.student_name ?? '').toLowerCase();
        const cpf = (s.student_cpf ?? '').replace(/\D/g, '');
        return name.includes(termRaw) || cpf.includes(termCpf);
      });
    }

    if (this.filterClass) {
      result = result.filter((s) => s.school_class_name === this.filterClass);
    }

    if (this.filterStatus && this.filterStatus !== 'all') {
      result = result.filter((s) => s.status === this.filterStatus);
    }

    this.filteredStudents = result;
  }

  clearStudentFilters(): void {
    this.searchStudent = '';
    this.filterClass = null;
    this.filterStatus = 'all';
    this.applyStudentFilters();
  }

  // ===================================
  // Linha do tempo / steps por aluno
  // ===================================

  /**
   * Retorna o "índice" do passo atual (0 a 3) com base nas datas
   */
  getStudentCurrentStep(student: AssessmentEventStudent): number {
    // Ordem: agendamento, convite, cadastro, prova realizada
    const stepsDone = [
      !!student.scheduled_date,
      !!student.invite_sent_at,
      !!student.credential_sent_at,
      !!student.completed_at,
    ];
    let lastDone = -1;
    stepsDone.forEach((done, index) => {
      if (done) lastDone = index;
    });

    if (lastDone === -1) return 0; // ainda nada feito
    if (lastDone === 3) return 3; // tudo concluído
    return lastDone + 1; // próximo passo "em andamento"
  }

  /**
   * Retorna a classe de cor para um step da linha do tempo
   */
  getStepClass(student: AssessmentEventStudent, stepIndex: number): string {
    const stepsDone = [
      !!student.scheduled_date,
      !!student.invite_sent_at,
      !!student.credential_sent_at,
      !!student.completed_at,
    ];
    const current = this.getStudentCurrentStep(student);

    if (stepsDone[stepIndex]) {
      return 'bg-green-500 border-green-500 text-white';
    }
    if (stepIndex === current) {
      return 'bg-orange-100 border-orange-400 text-orange-700';
    }
    return 'bg-gray-100 border-gray-300 text-gray-400';
  }

  getStepLabel(index: number): string {
    switch (index) {
      case 0:
        return 'Agendamento';
      case 1:
        return 'Convite';
      case 2:
        return 'Cadastro';
      case 3:
        return 'Realização';
      default:
        return '';
    }
  }

  getStepDate(student: AssessmentEventStudent, index: number): string {
    switch (index) {
      case 0:
        return this.formatDateTime(student.scheduled_date);
      case 1:
        return this.formatDateTime(student.invite_sent_at);
      case 2:
        return this.formatDateTime(student.credential_sent_at);
      case 3:
        return this.formatDateTime(student.completed_at);
      default:
        return '—';
    }
  }

  // ===================================
  // Modal: adicionar alunos ao evento
  // ===================================
  openAddStudentsModal(): void {
    this.isAddStudentsModalOpen = true;
    this.addStudentsError = null;
    this.addStudentsSuccess = null;
    this.addStudentsText = '';
    this.addStudentsMode = 'cpf';
  }

  closeAddStudentsModal(): void {
    this.isAddStudentsModalOpen = false;
    this.addStudentsError = null;
    this.addStudentsSuccess = null;
    this.addStudentsText = '';
  }

  submitAddStudents(): void {
    if (!this.event) return;
    this.addStudentsLoading = true;
    this.addStudentsError = null;
    this.addStudentsSuccess = null;

    const payload = {
      mode: this.addStudentsMode,
      data: this.addStudentsText,
    };

    const sub = this.assessmentService
      .addStudentsToEvent(this.event.id, payload)
      .subscribe({
        next: () => {
          this.addStudentsLoading = false;
          this.addStudentsSuccess = 'Alunos adicionados com sucesso!';
          this.loadStudents(this.event!.id);
          setTimeout(() => this.closeAddStudentsModal(), 1000);
        },
        error: (err) => {
          console.error('Erro ao adicionar alunos:', err);
          this.addStudentsLoading = false;
          this.addStudentsError =
            err?.error?.message ?? 'Falha ao adicionar alunos ao evento.';
        },
      });

    this.subs.push(sub);
  }

  // ===================================
  // Helpers de data para validação
  // ===================================
  private parseBrDate(dateStr: string | null | undefined): Date | null {
    if (!dateStr) return null;
    const parts = dateStr.split('/');
    if (parts.length !== 3) return null;
    const [dd, mm, yyyy] = parts.map((p) => Number(p));
    if (!dd || !mm || !yyyy) return null;
    return new Date(yyyy, mm - 1, dd, 0, 0, 0);
  }

  // ===================================
  // Modal: agendar prova do aluno
  // ===================================
  openScheduleModal(student: AssessmentEventStudent): void {
    this.scheduleStudentSelected = student;
    this.scheduleError = null;
    this.scheduleSuccess = null;
    this.scheduleLoading = false;

    // Sempre deixa o usuário escolher uma nova data/hora
    this.scheduleDateTime = '';
    this.isScheduleModalOpen = true;
  }

  closeScheduleModal(): void {
    this.isScheduleModalOpen = false;
    this.scheduleStudentSelected = null;
    this.scheduleError = null;
    this.scheduleSuccess = null;
    this.scheduleDateTime = '';
  }

  submitSchedule(): void {
    if (!this.event || !this.scheduleStudentSelected || !this.scheduleDateTime)
      return;

    // Validação contra datas limite do evento (se existirem)
    const ev: any = this.event;
    const start = this.parseBrDate(ev.scheduling_start_date ?? ev.exam_start_date);
    const end = this.parseBrDate(ev.scheduling_end_date ?? ev.exam_end_date);
    const chosen = new Date(this.scheduleDateTime);

    if (start && end) {
      // fim do dia para o limite final
      const endDay = new Date(end);
      endDay.setHours(23, 59, 59, 999);

      if (chosen < start || chosen > endDay) {
        this.scheduleError = `A data precisa estar entre ${this.formatDate(
          ev.scheduling_start_date ?? ev.exam_start_date
        )} e ${this.formatDate(ev.scheduling_end_date ?? ev.exam_end_date)}.`;
        return;
      }
    }

    this.scheduleLoading = true;
    this.scheduleError = null;
    this.scheduleSuccess = null;

    const payload = { scheduled_at: this.scheduleDateTime };

    const sub = this.assessmentService
      .scheduleStudent(this.event.id, this.scheduleStudentSelected.id, payload)
      .subscribe({
        next: () => {
          this.scheduleLoading = false;
          this.scheduleSuccess = 'Agendamento salvo com sucesso!';
          this.loadStudents(this.event!.id);
          setTimeout(() => this.closeScheduleModal(), 1000);
        },
        error: (err) => {
          console.error('Erro ao agendar aluno:', err);
          this.scheduleLoading = false;
          this.scheduleError =
            err?.error?.message ?? 'Falha ao salvar agendamento.';
        },
      });

    this.subs.push(sub);
  }

  // ===================================
  // Remover aluno do evento
  // ===================================
  removeStudent(student: AssessmentEventStudent): void {
    if (!this.event) return;
    const ok = confirm(
      `Remover o aluno "${student.student_name}" deste evento de avaliação?`
    );
    if (!ok) return;

    const sub = this.assessmentService
      .removeStudentFromEvent(this.event.id, student.id)
      .subscribe({
        next: () => {
          this.loadStudents(this.event!.id);
        },
        error: (err) => {
          console.error('Erro ao remover aluno:', err);
          alert('Falha ao remover aluno do evento.');
        },
      });

    this.subs.push(sub);
  }

  // ===================================
  // Navegação
  // ===================================
  backToList(): void {
    this.router.navigate(['/app/assessments']);
  }
}
