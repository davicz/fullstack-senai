import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { User } from '@models/index';

// Defina uma interface para a resposta da paginação da sua API
export interface PaginatedUsersResponse {
  data: User[];
  total: number;
  per_page: number;
  current_page: number;
  // ... outras props da paginação do Laravel
}

@Injectable({
  providedIn: 'root'
})
export class UserService {
  private apiUrl = 'http://sua-api.test/api/users'; // Substitua pela URL da sua API

  constructor(private http: HttpClient) { }

  // Filtra apenas status 'Finalizado' e ordena por nome
  getUsers(page: number, perPage: number, search?: string): Observable<PaginatedUsersResponse> {
    let params = new HttpParams()
      .set('page', page.toString())
      .set('per_page', perPage.toString())
      .set('status', 'Finalizado') // Requisito: filtrar por status
      .set('sort_by', 'name')     // Requisito: ordenar por nome
      .set('sort_dir', 'asc');

    if (search) {
      params = params.set('search', search);
    }

    return this.http.get<PaginatedUsersResponse>(this.apiUrl, { params });
  }

  inviteUser(email: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/invite`, { email });
  }

  updateUserProfile(userId: number, profile: string, password?: string): Observable<User> {
    const payload: any = { profile };
    if (password) {
      payload.password = password;
    }
    return this.http.put<User>(`${this.apiUrl}/${userId}/profile`, payload);
  }

  exportUsers(search?: string): Observable<Blob> {
    let params = new HttpParams().set('status', 'Finalizado');
    if (search) {
      params = params.set('search', search);
    }

    return this.http.get(`${this.apiUrl}/export`, {
      params,
      responseType: 'blob' // Importante para o download de arquivos
    });
  }
}