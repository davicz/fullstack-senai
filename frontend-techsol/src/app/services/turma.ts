// src/app/services/turma.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class TurmaService {
  private apiBase = 'http://localhost/api';

  constructor(private http: HttpClient) {}

  // LISTAR TURMAS (GET /classes)
  getTurmas(): Observable<any> {
    return this.http.get(`${this.apiBase}/classes`);
  }

  // BUSCAR UMA TURMA (GET /classes/{id})
  getTurma(id: number): Observable<any> {
    return this.http.get(`${this.apiBase}/classes/${id}`);
  }

  // CRIAR TURMA (POST /classes)
  createTurma(payload: any): Observable<any> {
    return this.http.post(`${this.apiBase}/classes`, payload);
  }

  // ATUALIZAR TURMA (PUT /classes/{id})
  updateTurma(id: number, payload: any): Observable<any> {
    return this.http.put(`${this.apiBase}/classes/${id}`, payload);
  }

  // EXCLUIR TURMA (DELETE /classes/{id})
  deleteTurma(id: number): Observable<any> {
    return this.http.delete(`${this.apiBase}/classes/${id}`);
  }

  // ADICIONAR PROFESSOR À TURMA
  addTeachers(classId: number, teacherIds: number[]): Observable<any> {
    return this.http.post(`${this.apiBase}/classes/${classId}/teachers`, {
      teacher_ids: teacherIds
    });
  }

  // ADICIONAR ALUNOS À TURMA
  addStudents(classId: number, studentIds: number[]): Observable<any> {
    return this.http.post(`${this.apiBase}/classes/${classId}/users`, {
      user_ids: studentIds
    });
  }

  // LISTAR ORIGENS
  getOrigens(): Observable<any> {
    return this.http.get(`${this.apiBase}/origens`);
  }

  // (Opcional) LISTAR TURNOS DA API – se quiser usar no futuro
  getTurnos(): Observable<any> {
    return this.http.get(`${this.apiBase}/turnos`);
  }

  // LISTAR DEPARTAMENTOS REGIONAIS
  getRegionalDepartments(): Observable<any> {
    return this.http.get(`${this.apiBase}/regional-departments`);
  }

  // LISTAR UNIDADES OPERACIONAIS
  getOperationalUnits(): Observable<any> {
    return this.http.get(`${this.apiBase}/operational-units`);
  }

  // LISTAR CURSOS
  getCursos(): Observable<any> {
    return this.http.get(`${this.apiBase}/courses`);
  }
}
