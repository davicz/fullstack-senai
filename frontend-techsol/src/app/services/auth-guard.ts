import { inject, PLATFORM_ID } from '@angular/core'; // 1. Importe PLATFORM_ID
import { isPlatformBrowser } from '@angular/common'; // 2. Importe isPlatformBrowser
import { CanActivateFn, Router } from '@angular/router';
import { Auth } from './auth';

export const authGuard: CanActivateFn = (route, state) => {
  const auth = inject(Auth);
  const router = inject(Router);
  const platformId = inject(PLATFORM_ID); // 3. Injete o Platform ID

  // 4. A VERIFICAÇÃO CRUCIAL
  if (isPlatformBrowser(platformId)) {
    // Só executa no navegador
    if (auth.getFinalToken()) {
      return true; // Usuário está logado, pode acessar
    } else {
      router.navigate(['/auth/login']); // Não está logado, vai pro login
      return false;
    }
  }

  // 5. Se estiver no SERVIDOR (SSR), sempre permite o acesso.
  // O Angular vai re-verificar no lado do cliente de qualquer forma.
  return true; 
};