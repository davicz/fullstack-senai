import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Auth } from './auth';

@Injectable({
  providedIn: 'root'
})
export class User {
  private apiUrl = 'http://localhost/api/users';

  constructor(
    private http: HttpClient,
    private auth: Auth
  ) {}

  /**
   * Busca lista paginada de usuários, com filtro opcional.
   */
  getUsers(page: number = 1, searchTerm: string = ''): Observable<any> {
    const token = this.auth.getFinalToken();

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    });

    let params = new HttpParams().set('page', page.toString()); // Adicionado o ;
    
    if (searchTerm) {
      params = params.set('search', searchTerm);
    }

    return this.http.get(this.apiUrl, { headers, params });
  }
}
