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
import { Reports } from '../app/pages/app/reports/reports';
import { AssessmentEventDetail } from './pages/app/assessments/assessment-event-detail';
import { Assessments } from './pages/app/assessments/assessments';
import { RegisterComponent } from './pages/auth/register/register';

export const routes: Routes = [
  // 1. Landing page (Home)
  { 
    path: '', 
    component: Landing 
  },

  // 2. Rota de Cadastro (Pública) - O Link do E-mail vai cair aqui
  { 
    path: 'register', 
    component: RegisterComponent,
    title: 'Finalizar Cadastro'
  },
  
  // 3. Rotas de Autenticação
  {
    path: 'auth',
    component: AuthLayoutComponent,
    children: [
      { path: 'login', component: Login },
      { path: 'select-profile', component: ProfileSelector },
      { path: '', redirectTo: 'login', pathMatch: 'full' },
    ]
  },

  // 4. Rotas Protegidas (Sistema Interno)
  {
    path: 'app',
    component: AppLayoutComponent,
    canActivate: [authGuard],
    children: [
      { path: 'convites', component: Invites, title: 'Convites' },
      { path: 'dashboard', component: Panel, title: 'Painel Inicial' },
      { path: 'turmas', component: Turmas },
      { path: 'turmas/:id', component: TurmaDetalhes },
      { path: 'escolas', component: OperatingUnits, title: 'Escolas' },
      { path: 'usuarios', component: Users }, // <--- Mantive apenas esta (a duplicada foi removida)
      { path: 'reports', component: Reports },
      { path: 'assessments/:id', component: AssessmentEventDetail },
      { path: 'assessments', component: Assessments },
      
      // Redirecionamento padrão dentro do /app
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
    ]
  },
  
  // 5. Rota Curinga (Sempre a última)
  { path: '**', redirectTo: '', pathMatch: 'full' }
];