import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost:8888/api';

  constructor(private http: HttpClient) { }

  login(credentials: {cpf: string, password: string}): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials).pipe(
      tap(response => this.saveToken(response))
    );
  }

  private saveToken(response: any) {
    if (response && response.access_token) {
      localStorage.setItem('auth_token', response.access_token);
    }
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }

  logout() {
    localStorage.removeItem('auth_token');
  }

  register(registrationData: any): Observable<any> { 
    return this.http.post(`${this.apiUrl}/register`, registrationData); 
  }
}