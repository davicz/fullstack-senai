import { Injectable, Inject, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject, tap, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class Auth {
  // URL Hardcoded conforme seu ambiente
  private apiUrl = 'http://localhost/api';

  private currentUserSubject = new BehaviorSubject<any>(null);
  public currentUser = this.currentUserSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router,
    @Inject(PLATFORM_ID) private platformId: Object
  ) {
    if (isPlatformBrowser(this.platformId)) {
      const user = localStorage.getItem('siac_user');
      if (user) {
        try {
          this.currentUserSubject.next(JSON.parse(user));
        } catch (e) {
          console.error('Erro ao fazer parse do usuário:', e);
        }
      }
    }
  }

  // ============================================================
  // CADASTRO (INVITE) - NOVO
  // ============================================================
  
  /**
   * Verifica se o token do convite é válido e retorna os dados prévios (email, cpf, roles)
   * Usado no ngOnInit do RegisterComponent
   */
  checkInvitationToken(token: string): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/invitation/${token}`);
  }

  /**
   * Envia os dados finais do formulário de cadastro para criar o usuário
   */
  register(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/register`, data);
  }

  // ============================================================
  // LOGIN
  // ============================================================
  login(credentials: { login: string, password: string }): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/login`, credentials).pipe(
      tap(response => {

        // --- LOGIN COM TOKEN FINAL ---
        if (response.access_token) {
          this.storeFinalAuth(response);
          this.router.navigate(['/app/dashboard']);
        }

        // --- LOGIN COM TOKEN TEMPORÁRIO ---
        else if (response.temporary_token) {
          // Salvar como siac_token para o interceptor usar nas próximas requisições
          if (isPlatformBrowser(this.platformId)) {
            localStorage.setItem('siac_token', response.temporary_token);
            localStorage.setItem('siac_user', JSON.stringify(response.user));
          }

          this.currentUserSubject.next(response.user);
          this.router.navigate(['/auth/select-profile']);
        }

      })
    );
  }

  // ============================================================
  // SELECT PROFILE
  // ============================================================
  selectProfile(userRoleId: number): Observable<any> {
    // O interceptor já enviará o token temporário armazenado
    return this.http
      .post<any>(
        `${this.apiUrl}/login/profile`,
        { user_role_id: userRoleId }
      )
      .pipe(
        tap(response => {
          // Substitui o temporary_token pelo final
          this.storeFinalAuth(response);

          // Atualiza o selected_role dentro do siac_user local
          if (isPlatformBrowser(this.platformId)) {
            const stored = localStorage.getItem('siac_user');
            let user = stored ? JSON.parse(stored) : {};
            user.selected_role = response.selected_role;
            
            localStorage.setItem('siac_user', JSON.stringify(user));
            this.currentUserSubject.next(user);
          }

          this.router.navigate(['/app/dashboard'], { replaceUrl: true });
        }),
        catchError(err => {
          console.error('Erro ao selecionar perfil:', err);
          return throwError(() => err);
        })
      );
  }

  // ============================================================
  // FINAL LOGOUT
  // ============================================================
  logout() {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('siac_token');
      localStorage.removeItem('siac_user');
      localStorage.removeItem('siac_temp_token'); // Limpeza extra por segurança
    }
    this.currentUserSubject.next(null);
    this.router.navigate(['/auth/login']);
  }

  // ============================================================
  // ARMAZENAMENTO AUXILIAR
  // ============================================================
  private storeFinalAuth(response: any) {
    if (isPlatformBrowser(this.platformId)) {
      // Esse token substitui o temporário automaticamente
      localStorage.setItem('siac_token', response.access_token);
      localStorage.setItem('siac_user', JSON.stringify(response.user));
    }
    this.currentUserSubject.next(response.user);
  }

  // ============================================================
  // GETTERS
  // ============================================================
  public getFinalToken(): string | null {
    return isPlatformBrowser(this.platformId)
      ? localStorage.getItem('siac_token')
      : null;
  }

  // ============================================================
  // LOGOUT SUAVE (TROCAR PERFIL)
  // ============================================================
  logoutToProfileSelection() {
    if (isPlatformBrowser(this.platformId)) {
      const finalToken = localStorage.getItem('siac_token');

      if (finalToken) {
        // Transforma o token final em temporário logicamente (apenas renomeando uso se necessário)
        // Na prática, o backend deve aceitar o token atual para permitir a troca, 
        // ou você deve ter guardado o token 'pai' em algum lugar.
        // Mantive sua lógica original aqui:
        localStorage.setItem('siac_temp_token', finalToken);
        localStorage.setItem('siac_token', finalToken); 
      }
    }

    this.currentUserSubject.next(null);
    this.router.navigate(['/auth/select-profile']);
  }
}