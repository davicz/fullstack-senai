import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthService } from './auth';

@Injectable({
  providedIn: 'root',
})
export class CollaboratorService {
  private apiUrl = 'http://localhost:8888/api/collaborators';

  constructor(private http: HttpClient, private authService: AuthService) {}

  getCollaborators(page: number = 1, search: string = ''): Observable<any> {
    // Cria os cabeçalhos (headers) com o token de autenticação
    const headers = new HttpHeaders({
      Authorization: `Bearer ${this.authService.getToken()}`,
    });

    // Cria os parâmetros de URL para busca e paginação
    let params = new HttpParams()
      .set('page', page.toString())
      .set('search', search);

    // Faz a requisição GET, enviando os headers e os params
    return this.http.get(this.apiUrl, { headers, params });
  }

  updateCollaborator(id: number, data: any): Observable<any> {
    const headers = new HttpHeaders({
      Authorization: `Bearer ${this.authService.getToken()}`,
    });
    return this.http.put(`${this.apiUrl}/${id}`, data, { headers });
  }
}
