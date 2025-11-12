import { Routes } from '@angular/router';
import { AuthLayoutComponent } from './layouts/auth/auth';
import { Login } from './pages/auth/login/login';
import { ProfileSelector } from './pages/auth/profile-selector/profile-selector';
import { AppLayoutComponent } from './layouts/app/app';
import { Invites } from './pages/app/invites/invites';
import { Panel } from './pages/app/panel/panel';
import { Users } from './pages/app/users/users';
import { authGuard } from './services/auth-guard';

export const routes: Routes = [
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
      { path: 'invites', component: Invites },
      { path: 'dashboard', component: Panel },
      {
        path: 'users',
        loadComponent: () =>
          import('./pages/app/users/users').then(m => m.Users),
      },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  },
  { path: '', redirectTo: 'auth', pathMatch: 'full' },
];
