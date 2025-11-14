// src/app/interceptors/auth.ts
import { HttpInterceptorFn } from '@angular/common/http';

/**
 * Interceptor simples que pega o token do localStorage e adiciona no header.
 * - Prioriza 'siac_token'
 * - Fallback para 'auth_token' caso exista (compatibilidade)
 *
 * Observações:
 * - Protegemos o acesso ao localStorage verificando typeof window !== 'undefined'
 * - Não acessa platformId para manter o interceptor simples e compatível com provideHttpClient
 */
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  let token: string | null = null;

  if (typeof window !== 'undefined' && typeof localStorage !== 'undefined') {
    token = localStorage.getItem('siac_token') || localStorage.getItem('auth_token') || null;
  }

  if (token) {
    const cloned = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    });
    return next(cloned);
  }

  return next(req);
};
