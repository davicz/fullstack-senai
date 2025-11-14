// src/app/services/user.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { Auth } from './auth';

@Injectable({
  providedIn: 'root'
})
export class User {
  // base URL (ajuste se sua API usa outra porta/host)
  private apiUrl = 'http://localhost/api/users';

  constructor(
    private http: HttpClient,
    private auth: Auth
  ) {}

  private getAuthHeaders(): HttpHeaders {
    const token = this.auth.getFinalToken();
    const headersObj: any = { 'Accept': 'application/json' };
    if (token) headersObj['Authorization'] = `Bearer ${token}`;
    return new HttpHeaders(headersObj);
  }

  /**
   * getUsers: traz users com paginação/opcionais
   * - page: número da página
   * - search: string ou null
   * - roleId: string|number|null
   */
  getUsers(page: number = 1, search?: string | null, roleId?: string | number | null): Observable<any> {
    let params = new HttpParams().set('page', page.toString());

    if (search && String(search).trim() !== '') {
      params = params.set('search', String(search).trim());
    }

    if (roleId !== null && roleId !== undefined && String(roleId).trim() !== '') {
      params = params.set('role_id', String(roleId));
    }

    // Log simples para debug (ver no console do navegador)
    console.debug('[UserService] GET', this.apiUrl, { params: params.toString(), tokenPresent: !!this.auth.getFinalToken() });

    return this.http.get<any>(this.apiUrl, { headers: this.getAuthHeaders(), params }).pipe(
      // opcional: log na resposta para ajudar debug
      tap(resp => console.debug('[UserService] response', resp))
    );
  }

  /**
   * updateUser: atualiza usuário por id
   * - payload deve corresponder ao que o backend espera:
   *    { name: '...', roles: [1,2,3] }
   */
  updateUser(userId: number, data: any) {
    const url = `${this.apiUrl}/${userId}`;
    console.debug('[UserService] PUT', url, data);
    return this.http.put<any>(url, data, { headers: this.getAuthHeaders() });
  }
}
