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
      if (user) this.currentUserSubject.next(JSON.parse(user));
    }
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
          // 🔥 CORREÇÃO CRUCIAL: salvar como siac_token para o interceptor usar
          localStorage.setItem('siac_token', response.temporary_token);

          localStorage.setItem('siac_user', JSON.stringify(response.user));

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
    // sempre usa o token que o interceptor já está enviando
    return this.http
      .post<any>(
        `${this.apiUrl}/login/profile`,
        { user_role_id: userRoleId }
      )
      .pipe(
        tap(response => {
          // substitui o temporary_token pelo final
          this.storeFinalAuth(response);

          // salvar o selected_role dentro do siac_user
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
    }
    this.currentUserSubject.next(null);
    this.router.navigate(['/auth/login']);
  }

  // ============================================================
  // ARMAZENAMENTO
  // ============================================================
  private storeFinalAuth(response: any) {
    if (isPlatformBrowser(this.platformId)) {
      // 🔥 esse token substitui o temporário automaticamente
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

// --- LOGOUT SUAVE (TROCAR PERFIL) ---
logoutToProfileSelection() {
  if (isPlatformBrowser(this.platformId)) {

    const finalToken = localStorage.getItem('siac_token');

    if (finalToken) {
      // transforma o token final em temporário
      localStorage.setItem('siac_temp_token', finalToken);

      // 🔥 o interceptor só lê siac_token → então colocamos o temporário lá também
      localStorage.setItem('siac_token', finalToken);
    }
  }

  this.currentUserSubject.next(null);
  this.router.navigate(['/auth/select-profile']);
}

}
