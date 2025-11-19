import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class OperatingUnitsService {
  private apiBase = 'http://localhost/api';

  constructor(private http: HttpClient) {}

  // --- CRUD PRINCIPAL DE UNIDADES OPERACIONAIS ---

  // LISTAR TODAS (GET /operational-units)
  getOperatingUnits(): Observable<any> {
    return this.http.get(`${this.apiBase}/operational-units`);
  }

  // BUSCAR UMA UNIDADE ESPECÍFICA (GET /operational-units/{id})
  getOperatingUnit(id: number): Observable<any> {
    return this.http.get(`${this.apiBase}/operational-units/${id}`);
  }

  // CRIAR NOVA UNIDADE (POST /operational-units)
  createOperatingUnit(payload: any): Observable<any> {
    return this.http.post(`${this.apiBase}/operational-units`, payload);
  }

  // ATUALIZAR UNIDADE (PUT /operational-units/{id})
  updateOperatingUnit(id: number, payload: any): Observable<any> {
    return this.http.put(`${this.apiBase}/operational-units/${id}`, payload);
  }

  // EXCLUIR UNIDADE (DELETE /operational-units/{id})
  deleteOperatingUnit(id: number): Observable<any> {
    return this.http.delete(`${this.apiBase}/operational-units/${id}`);
  }

  // --- DEPENDÊNCIAS ---

  // LISTAR DEPARTAMENTOS REGIONAIS (Necessário para o <select> do formulário)
  getRegionalDepartments(): Observable<any> {
    return this.http.get(`${this.apiBase}/regional-departments`);
  }
}