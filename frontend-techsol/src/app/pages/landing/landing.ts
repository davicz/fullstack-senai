import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';

@Component({
  selector: 'app-landing',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './landing.html',
  styleUrl: './landing.css'
})
export class Landing {
  isDarkMode = signal(false);
  openFaqIndex = signal<number | null>(null);

  faqs = [
    {
      question: 'Como obtenho acesso ao SIAC?',
      answer: 'Para criar acesso ao sistema SIAC, é necessário ser convidado por um de nossos administradores. Com o intuito de ter acesso, recomendamos que: 1. Esclareça contato com o administrador associado a sua instituição de ensino; 2. Solicite um convite de acesso ao SIAC.'
    },
    {
      question: 'Como recuperar minha conta?',
      answer: 'Caso tenha esquecido sua senha, clique no link "Esqueceu a senha?" na página de login e siga as instruções enviadas para seu e-mail cadastrado.'
    },
    {
      question: 'Minha conta foi bloqueada',
      answer: 'Entre em contato com o administrador da sua instituição para solicitar o desbloqueio da sua conta.'
    },
    {
      question: 'Área de interesse e curso',
      answer: 'Selecione sua área de interesse e curso durante o cadastro para personalizar sua experiência no sistema.'
    }
  ];

  constructor(private router: Router) {}

  toggleTheme(): void {
    this.isDarkMode.update(value => !value);
    // Aqui você pode implementar a lógica real de tema
    document.documentElement.classList.toggle('dark');
  }

  toggleFaq(index: number): void {
    if (this.openFaqIndex() === index) {
      this.openFaqIndex.set(null);
    } else {
      this.openFaqIndex.set(index);
    }
  }

  navigateToLogin(): void {
    this.router.navigate(['/auth/login']);
  }

  scrollToSection(sectionId: string): void {
    const element = document.getElementById(sectionId);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
}