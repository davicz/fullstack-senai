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

  // GET /turmas - Lista todas as turmas
  getTurmas(): Observable<any> {
    return this.http.get(`${this.apiBase}/turmas`);
  }

  // GET /turmas/{id} - Busca uma turma específica
  getTurma(id: number): Observable<any> {
    return this.http.get(`${this.apiBase}/turmas/${id}`);
  }

  // POST /turmas - Cria uma nova turma
  createTurma(payload: any): Observable<any> {
    return this.http.post(`${this.apiBase}/turmas`, payload);
  }

  // PUT /turmas/{id} - Atualiza uma turma
  updateTurma(id: number, payload: any): Observable<any> {
    return this.http.put(`${this.apiBase}/turmas/${id}`, payload);
  }

  // DELETE /turmas/{id} - Exclui uma turma
  deleteTurma(id: number): Observable<any> {
    return this.http.delete(`${this.apiBase}/turmas/${id}`);
  }

  // GET /origens - Lista as origens disponíveis
  getOrigens(): Observable<any> {
    return this.http.get(`${this.apiBase}/origens`);
  }

  // GET /regional-departments - Lista os departamentos regionais
  getRegionalDepartments(): Observable<any> {
    return this.http.get(`${this.apiBase}/regional-departments`);
  }

  // GET /operational-units - Lista as unidades operacionais
  getOperationalUnits(): Observable<any> {
    return this.http.get(`${this.apiBase}/operational-units`);
  }

  // GET /cursos - Lista os cursos disponíveis
  getCursos(): Observable<any> {
    return this.http.get(`${this.apiBase}/cursos`);
  }
}