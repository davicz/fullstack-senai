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
  styleUrl: './login.css',
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
        // 4. O FIM DO LOOP: Parar o loading imediatamente ao receber o erro
        this.isLoading = false; 
        
        console.error('Erro recebido:', err);

        // 5. Tratamento Específico do Erro 422 (Validação do Laravel)
        if (err.status === 422) {
          // O Laravel coloca os erros específicos dentro de err.error.errors
          const validationErrors = err.error.errors;

          // Verifica se existe erro especificamente no campo 'email'
          if (validationErrors && validationErrors.email) {
            // Pega a primeira mensagem de erro do array de emails
            this.errorMessage = validationErrors.email[0]; 
          } else if (err.error.message) {
            // Fallback para a mensagem genérica do Laravel
            this.errorMessage = err.error.message;
          } else {
            this.errorMessage = 'Dados inválidos. Verifique o e-mail digitado.';
          }
        } 
        // Tratamento para erro 401 (Credenciais erradas mas formato válido)
        else if (err.status === 401) {
          this.errorMessage = 'E-mail ou senha incorretos.';
        } 
        // Erros de Servidor ou Conexão (500, 0)
        else {
          this.errorMessage = 'Erro no servidor. Tente novamente.';
        }
      }
    });

   
  }
}