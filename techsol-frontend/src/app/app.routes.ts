import { Routes } from '@angular/router';
import { Landing } from './pages/landing/landing';
import { Login } from './pages/login/login';
import { Register } from './pages/register/register';
import { Dashboard } from './pages/admin/dashboard/dashboard'; // Importa o dashboard
import { authGuard } from './services/auth-guard'; // Importa o guarda
import { Invite } from './pages/admin/invite/invite';
import { UserList } from './pages/admin/components/user-list/user-list';


export const routes: Routes = [
  // Rota padrão que carrega a Landing Page
  { path: '', component: Landing },
  { path: 'login', component: Login },
  { path: 'register/:token', component: Register },
  { 
    path: 'admin', 
    component: Dashboard,
    // canActivate: [authGuard],
    children: [
      {path: '', redirectTo: 'users', pathMatch: 'full'},
      {path: 'users', component: UserList },
    ]
  }, 
  { 
    path: 'admin/invite',
    component: Invite,
    canActivate: [authGuard] 
  }, 
];
