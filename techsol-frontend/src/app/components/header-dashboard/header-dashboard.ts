import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-header-dashboard',
  imports: [CommonModule],
  templateUrl: './header-dashboard.html',
  styleUrl: './header-dashboard.css'
})
export class HeaderDashboard {
  isDropdownOpen = false;

  toggleDropdown(): void {
    this.isDropdownOpen = !this.isDropdownOpen;
  }

  logout(): void {
    console.log('User logging out...');
    // Aqui você chamará o seu serviço de autenticação para limpar o token/sessão
    // e redirecionar para a página de login.
    this.isDropdownOpen = false;
  }
}
