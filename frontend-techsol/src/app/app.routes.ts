import { Routes } from '@angular/router';
import { AuthLayoutComponent } from './layouts/auth/auth';
import { Login } from './pages/auth/login/login';
import { ProfileSelector } from './pages/auth/profile-selector/profile-selector';
import { AppLayoutComponent } from './layouts/app/app';
import { Invites } from './pages/app/invites/invites';
import { Panel } from './pages/app/panel/panel';
import { Turmas } from './pages/app/turmas/turmas';
import { OperatingUnits } from './pages/app/operating-units/operating-units';
import { authGuard } from './services/auth-guard';
import { Landing } from './pages/landing/landing';
import { TurmaDetalhes } from './pages/app/turmas/turma-detalhes';
import { Users } from './pages/app/users/users';

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
  {
    path: 'app',
    component: AppLayoutComponent,
    canActivate: [authGuard],
    children: [
      { path: 'convites', component: Invites, title: 'Convites' },
      { path: 'dashboard', component: Panel, title: 'Painel Inicial' },
      { path: 'turmas', component: Turmas },
      { path: 'turmas/:id', component: TurmaDetalhes },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
      { path: 'escolas', component: OperatingUnits, title: 'Escolas' },
      { path: 'users', component: Users },
      {
        path: 'usuarios',
        loadComponent: () =>
          import('./pages/app/users/users').then(m => m.Users),
      },
    ]
  },
  
  // Redireciona qualquer rota não encontrada para a landing page
  { path: '**', redirectTo: '', pathMatch: 'full' }
];