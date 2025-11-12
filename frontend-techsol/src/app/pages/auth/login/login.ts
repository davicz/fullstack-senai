import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router'; // Importe RouterModule
import { FormsModule } from '@angular/forms';
import { Auth } from '../../../services/auth'; // Importe nosso serviço

@Component({
  selector: 'app-login-component',
  standalone: true, // Adicionado 'standalone: true'
  imports: [
    CommonModule, 
    FormsModule, 
    RouterModule // Adicione RouterModule para o routerLink
  ],
  templateUrl: './login.html',
  styleUrl: './login.css', // O arquivo do Guilherme é login.css, não auth.css [cite: login.ts]
})
export class Login {
  login = '';
  password = '';
  
  // Variáveis de estado para feedback do usuário
  isLoading = false;
  errorMessage: string | null = null;

  constructor(
    private router: Router,
    private auth: Auth // Injeta o serviço de autenticação
  ) {}

  onSubmit(): void {
    if (!this.login || !this.password) {
      this.errorMessage = "Por favor, preencha todos os campos.";
      return;
    }

    this.isLoading = true;
    this.errorMessage = null;

    // Prepara os dados para enviar à API
    const credentials = {
      login: this.login,
      password: this.password
    };

    // Chama o serviço de login
    this.auth.login(credentials).subscribe({
      next: () => {
        // Sucesso! O serviço de auth já cuida do redirecionamento
        this.isLoading = false;
      },
      error: (err) => {
        // Trata o erro da API
        console.error('Erro no login:', err);
        this.errorMessage = err.error?.message || 'CPF/E-mail ou senha inválidos.';
        this.isLoading = false;
      }
    });
  }
}