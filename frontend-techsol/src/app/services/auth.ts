import { Injectable, Inject, PLATFORM_ID } from '@angular/core'; // Importações adicionadas
import { isPlatformBrowser } from '@angular/common'; // Importação adicionada
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject, tap } from 'rxjs';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class Auth {
  // ATENÇÃO: Verifique a porta da sua API.
  private apiUrl = 'http://localhost/api'; 

  private currentUserSubject = new BehaviorSubject<any>(null);
  public currentUser = this.currentUserSubject.asObservable();

  private tempTokenSubject = new BehaviorSubject<string | null>(null);
  public tempToken = this.tempTokenSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router,
    @Inject(PLATFORM_ID) private platformId: Object // Injeta o PLATFORM_ID
  ) {
    // Tenta carregar o usuário APENAS se estivermos no NAVEGADOR
    if (isPlatformBrowser(this.platformId)) {
      const user = localStorage.getItem('siac_user');
      if (user) {
        this.currentUserSubject.next(JSON.parse(user));
      }
    }
  }

  // --- MÉTODOS PRINCIPAIS ---
  // (O restante dos métodos de login e selectProfile permanecem os mesmos)
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

  selectProfile(userRoleId: number): Observable<any> {
    const token = this.getTempToken();
    if (!token) {
      this.router.navigate(['/auth/login']);
      return new Observable();
    }
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    });
    return this.http.post<any>(`${this.apiUrl}/login/profile`, { user_role_id: userRoleId }, { headers }).pipe(
      tap(response => {
        this.storeFinalAuth(response);
        this.router.navigate(['/app/dashboard']);
      })
    );
  }

  logout() {
    // Adiciona a verificação de plataforma em todos os lugares que usam localStorage
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('siac_token');
      localStorage.removeItem('siac_user');
    }
    this.currentUserSubject.next(null);
    this.router.navigate(['/auth/login']);
  }

  // --- MÉTODOS DE ARMAZENAMENTO (Privados) ---

  private storeFinalAuth(response: any) {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.setItem('siac_token', response.access_token);
      localStorage.setItem('siac_user', JSON.stringify(response.user));
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

  // --- MÉTODOS DE LEITURA (Públicos) ---

  public getFinalToken(): string | null {
    if (isPlatformBrowser(this.platformId)) {
      return localStorage.getItem('siac_token');
    }
    return null;
  }

  public getTempToken(): string | null {
    if (isPlatformBrowser(this.platformId)) {
      return localStorage.getItem('siac_temp_token');
    }
    return null;
  }
}