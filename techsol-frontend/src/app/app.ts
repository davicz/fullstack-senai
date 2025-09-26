import { Component, signal, effect, WritableSignal } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { AppView, User } from '@models/index';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterModule],
  templateUrl: './app.html',
  styleUrl: './app.css',
})
export class App {
  title = 'frontend';
  currentView = signal<AppView>('landing');
  isDarkMode = signal(true);
  currentUser = signal<User | null>(null);
  headerScrolled = signal(false);
  loginError = signal<string | null>(null);

  constructor(private router: Router) {
    effect(() => { document.documentElement.classList.toggle('dark', this.isDarkMode()); });
    window.addEventListener('scroll', () => { this.headerScrolled.set(window.scrollY > 50); });
  }
  toggleTheme() { this.isDarkMode.update(value => !value); }

  onLoginClicked() {
  this.router.navigate(['/login']);
  }

  private mockUsers: WritableSignal<User[]> = signal([
    { id: 1, name: 'Admin Master', email: 'admin@ts.dev', cpf: '111.111.111-11', profile: 'Administrador', password: 'Password123!' },
    { id: 2, name: 'Maria Cultura', email: 'rh@ts.dev', cpf: '222.222.222-22', profile: 'Gente e Cultura', password: 'Password123!' },
  ]);

  handleLogin(credentials: {cpf: string, password: string}) {
    const user = this.mockUsers().find(u => u.cpf === credentials.cpf && u.password === credentials.password);
    if (user) {
      this.currentUser.set(user);
      this.currentView.set('dashboard');
      this.loginError.set(null);
    } else {
      this.loginError.set('CPF ou senha inválidos.');
    }
  }

  handleLogout() {
    this.currentUser.set(null);
    this.currentView.set('landing');
  }

}
