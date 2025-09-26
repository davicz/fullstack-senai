import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from './auth';

export const authGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (authService.isLoggedIn()) {
    return true; // Se está logado, permite o acesso
  } else {
    router.navigate(['/login']); // Se não está logado, redireciona para o login
    return false; // E bloqueia o acesso à rota originalS
  }
};