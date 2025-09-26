import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthService } from './auth';

@Injectable({
  providedIn: 'root'
})
export class InviteService {
  private apiUrl = 'http://localhost:8888/api/invites';

  constructor(private http: HttpClient, private authService: AuthService) { }

  sendInvite(email: string): Observable<any> {
    // Cria os cabeçalhos com o token de autenticação
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${this.authService.getToken()}`
    });

    // O corpo da requisição contém o e-mail a ser convidado
    const body = { email: email };

    // Faz a requisição POST, enviando o corpo e os cabeçalhos
    return this.http.post(this.apiUrl, body, { headers });
  }
}