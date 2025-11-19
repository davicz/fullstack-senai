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

  // TURMAS
  getTurmas(): Observable<any> {
    return this.http.get(`${this.apiBase}/classes`);
  }

  getTurma(id: number): Observable<any> {
    return this.http.get(`${this.apiBase}/classes/${id}`);
  }

  createTurma(payload: any): Observable<any> {
    return this.http.post(`${this.apiBase}/classes`, payload);
  }

  updateTurma(id: number, payload: any): Observable<any> {
    return this.http.put(`${this.apiBase}/classes/${id}`, payload);
  }

  deleteTurma(id: number): Observable<any> {
    return this.http.delete(`${this.apiBase}/classes/${id}`);
  }

  // LISTAS AUXILIARES
  getOrigens(): Observable<any> {
    return this.http.get(`${this.apiBase}/origens`);
  }

  getTurnos(): Observable<any> {
    return this.http.get(`${this.apiBase}/turnos`);
  }

  getRegionalDepartments(): Observable<any> {
    return this.http.get(`${this.apiBase}/regional-departments`);
  }

  getOperationalUnits(): Observable<any> {
    return this.http.get(`${this.apiBase}/operational-units`);
  }

  getCursos(): Observable<any> {
    return this.http.get(`${this.apiBase}/courses`);
  }

  // USUÁRIOS (para seleção de professores/alunos)
  getUsers(): Observable<any> {
    return this.http.get(`${this.apiBase}/users`);
  }

  // PROFESSORES
  addTeachers(classId: number, teacherIds: number[]): Observable<any> {
    return this.http.post(`${this.apiBase}/classes/${classId}/teachers`, {
      teacher_ids: teacherIds
    });
  }

  removeTeacher(classId: number, teacherId: number): Observable<any> {
    return this.http.delete(`${this.apiBase}/classes/${classId}/teachers/${teacherId}`);
  }

  // ALUNOS
  addStudents(classId: number, studentIds: number[]): Observable<any> {
    return this.http.post(`${this.apiBase}/classes/${classId}/students`, {
      user_ids: studentIds
    });
  }

  removeStudent(classId: number, studentId: number): Observable<any> {
    return this.http.delete(`${this.apiBase}/classes/${classId}/students/${studentId}`);
  }
}
