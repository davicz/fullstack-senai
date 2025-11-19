// src/app/services/assessment.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface AssessmentEvent {
  id: number;
  type: 'saep' | 'dr' | 'dn';
  name: string;
  year: number;
  semester: number;
  start_date: string;              // data início evento
  end_date: string;                // data fim evento
  scheduling_start_date: string;   // início agendamento
  scheduling_end_date: string;     // fim agendamento
  status: 'open' | 'closed' | 'scheduled' | 'draft';

  // Resumo dos alunos
  total_students: number;
  invited_students: number;
  scheduled_students: number;
  completed_students: number;
}

export interface AssessmentEventStudent {
  id: number;
  user_id: number;

  // Dados do aluno
  student_name: string;
  student_cpf: string;

  // Turma / Curso / Unidades
  school_class_id: number;
  school_class_name: string;
  course_name: string;
  dr_name: string;
  uo_name: string;

  // Linha do tempo
  scheduled_date: string | null;       // agendamento
  invite_sent_at: string | null;       // convite
  credential_sent_at: string | null;   // cadastro concluído (equivalente ao "register")
  credential_code: string | null;
  completed_at: string | null;         // prova realizada

  // Status gerais
  scheduling_status: string;
  credential_status: string;
  exam_status: string;
  status: string;
}


@Injectable({
  providedIn: 'root'
})
export class AssessmentService {
  private apiBase = 'http://localhost/api';

  constructor(private http: HttpClient) {}

  // =====================================================
  // EVENTOS (lista) – usado na página de listagem (etapa 1)
  // =====================================================

  getEvents(params?: any): Observable<any> {
    let httpParams = new HttpParams();
    if (params) {
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
          httpParams = httpParams.set(key, params[key]);
        }
      });
    }

    return this.http.get(`${this.apiBase}/assessment-events`, { params: httpParams });
  }

  // =====================================================
  // DETALHE DO EVENTO (etapa 2)
  // =====================================================

  /**
   * Busca dados completos de um evento de avaliação (SAEP/DR/DN)
   */
  getEvent(eventId: number): Observable<AssessmentEvent> {
    return this.http.get<AssessmentEvent>(`${this.apiBase}/assessment-events/${eventId}`);
  }

  /**
   * Lista de alunos vinculados ao evento, com dados de agendamento / realização
   * Aceita filtros (search, status, class_id, etc.)
   */
  getEventStudents(eventId: number, filters?: any): Observable<any> {
    let httpParams = new HttpParams();
    if (filters) {
      Object.keys(filters).forEach(key => {
        if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
          httpParams = httpParams.set(key, filters[key]);
        }
      });
    }

    return this.http.get(`${this.apiBase}/assessment-events/${eventId}/students`, {
      params: httpParams
    });
  }

  /**
   * Adiciona alunos ao evento (payload flexível: lista de IDs, importação, etc.)
   */
  addStudentsToEvent(eventId: number, payload: any): Observable<any> {
    return this.http.post(`${this.apiBase}/assessment-events/${eventId}/students`, payload);
  }

  /**
   * Remove um aluno do evento
   */
  removeStudentFromEvent(eventId: number, studentId: number): Observable<any> {
    return this.http.delete(
      `${this.apiBase}/assessment-events/${eventId}/students/${studentId}`
    );
  }

  /**
   * Agenda a prova de um aluno em um evento
   */
  scheduleStudent(
    eventId: number,
    studentId: number,
    payload: { scheduled_at: string }
  ): Observable<any> {
    return this.http.post(
      `${this.apiBase}/assessment-events/${eventId}/students/${studentId}/schedule`,
      payload
    );
  }

  /**
   * (Opcional) Reenvia convite para o aluno
   */
  resendInvite(eventId: number, studentId: number): Observable<any> {
    return this.http.post(
      `${this.apiBase}/assessment-events/${eventId}/students/${studentId}/resend-invite`,
      {}
    );
  }
}
