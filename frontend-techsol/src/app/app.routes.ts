import { Routes } from '@angular/router';
import { AuthLayoutComponent } from './layouts/auth/auth';
import { Login } from './pages/auth/login/login';
import { ProfileSelector } from './pages/auth/profile-selector/profile-selector';
import { AppLayoutComponent } from './layouts/app/app';
import { Invites } from './pages/app/invites/invites';
import { Panel } from './pages/app/panel/panel';

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
    children: [
      { path: 'invites', component: Invites },
      // (No futuro: /app/users, /app/schools, etc.)
      { path : 'dashboard', component: Panel },

      // Redireciona '.../app' para '.../app/panel'
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  },

  // Redireciona a raiz do site ('/') para '/auth/login'
  { path: '', redirectTo: 'auth', pathMatch: 'full' },
];
