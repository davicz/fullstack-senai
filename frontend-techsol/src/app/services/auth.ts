import { Injectable, Inject, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject, tap, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class Auth {
  private apiUrl = 'http://localhost/api'; // Verifique sua porta

  private currentUserSubject = new BehaviorSubject<any>(null);
  public currentUser = this.currentUserSubject.asObservable();

  private tempTokenSubject = new BehaviorSubject<string | null>(null);
  public tempToken = this.tempTokenSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router,
    @Inject(PLATFORM_ID) private platformId: Object
  ) {
    if (isPlatformBrowser(this.platformId)) {
      const user = localStorage.getItem('siac_user');
      if (user) this.currentUserSubject.next(JSON.parse(user));
    }
  }

  // --- LOGIN ---
  login(credentials: { login: string, password: string }): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/login`, credentials).pipe(
      tap(response => {
        if (response.access_token) {
          this.storeFinalAuth(response);
          this.router.navigate(['/app/dashboard']);
        } else if (response.temporary_token) {
          this.storeTemporaryAuth(response);
          this.router.navigate(['/auth/select-profile']);
        }
      })
    );
  }

  // --- SELEÇÃO DE PERFIL ---
  selectProfile(userRoleId: number): Observable<any> {
    const token = this.getTempToken();
    if (!token) {
      this.router.navigate(['/auth/login']);
      return throwError(() => new Error('Token temporário ausente'));
    }
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`, 'Accept': 'application/json'
    });
    return this.http.post<any>(`${this.apiUrl}/login/profile`, { user_role_id: userRoleId }, { headers }).pipe(
      tap(response => {
        this.storeFinalAuth(response);
        this.router.navigate(['/app/dashboard'], { replaceUrl: true });
      }),
      catchError(err => {
        console.error('Erro ao selecionar perfil:', err);
        return throwError(() => err);
      })
    );
  }

  // --- LOGOUT COMPLETO ---
  logout() {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('siac_token');
      localStorage.removeItem('siac_temp_token');
      localStorage.removeItem('siac_user');
    }
    this.currentUserSubject.next(null);
    this.router.navigate(['/auth/login']);
  }

  // --- LOGOUT SUAVE (TROCAR PERFIL) ---
  logoutToProfileSelection() {
    if (isPlatformBrowser(this.platformId)) {
      const currentFinalToken = localStorage.getItem('siac_token');

      // Se o usuário já tem um token final (login normal)
      if (currentFinalToken) {
        // Reaproveita o token final como temporário
        localStorage.setItem('siac_temp_token', currentFinalToken);
      }

      // Remove o token final para forçar nova seleção de perfil
      localStorage.removeItem('siac_token');
    }

    // Mantém os dados do usuário (siac_user)
    this.currentUserSubject.next(null);

    // Volta para a tela de seleção de perfil
    this.router.navigate(['/auth/select-profile']);
  }

  // --- ARMAZENAMENTO ---
  private storeFinalAuth(response: any) {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.setItem('siac_token', response.access_token);
      localStorage.setItem('siac_user', JSON.stringify(response.user));
      // APAGA O TOKEN TEMPORÁRIO AQUI, pois ele já foi usado
      localStorage.removeItem('siac_temp_token');
    }
    this.currentUserSubject.next(response.user);
    this.tempTokenSubject.next(null);
  }

  private storeTemporaryAuth(response: any) {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.setItem('siac_temp_token', response.temporary_token);
      localStorage.setItem('siac_user', JSON.stringify(response.user));
    }
    this.currentUserSubject.next(response.user);
  }

  // --- GETTERS ---
  public getFinalToken(): string | null {
    return isPlatformBrowser(this.platformId)
      ? localStorage.getItem('siac_token')
      : null;
  }

  public getTempToken(): string | null {
    return isPlatformBrowser(this.platformId)
      ? localStorage.getItem('siac_temp_token')
      : null;
  }
}