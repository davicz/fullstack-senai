import { Routes } from '@angular/router';
import { AuthLayoutComponent } from './layouts/auth/auth';
import { Login } from './pages/auth/login/login';
import { ProfileSelector } from './pages/auth/profile-selector/profile-selector';
import { AppLayoutComponent } from './layouts/app/app';
import { Invites } from './pages/app/invites/invites';
import { Panel } from './pages/app/panel/panel';
import { Turmas } from './pages/app/turmas/turmas';
import { authGuard } from './services/auth-guard';
import { Landing } from './pages/landing/landing';

export const routes: Routes = [
  // Landing page (home)
  { 
    path: '', 
    component: Landing 
  },
  
  // Rotas de autenticação
  {
    path: 'auth',
    component: AuthLayoutComponent,
    children: [
      { path: 'login', component: Login },
      { path: 'select-profile', component: ProfileSelector },
      { path: '', redirectTo: 'login', pathMatch: 'full' }
    ]
  },
  
  // Rotas da aplicação (protegidas)
  {
    path: 'app',
    component: AppLayoutComponent,
    canActivate: [authGuard],
    children: [
      { path: 'invites', component: Invites },
      { path: 'dashboard', component: Panel },
      { path: 'turmas', component: Turmas },
      {
        path: 'users',
        loadComponent: () =>
          import('./pages/app/users/users').then(m => m.Users),
      },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  },
  
  // Redireciona qualquer rota não encontrada para a landing page
  { path: '**', redirectTo: '', pathMatch: 'full' }
];