import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-login-component',
  imports: [CommonModule, FormsModule],
  templateUrl: './login.html',
  styleUrl: './login.css',
})
export class LoginComponent {
  login = '';
  password = '';

  constructor(private router: Router) {}

  onSubmit(): void {
    console.log('Tentativa de login com:', this.login);
    // Navigate to another page upon successful login
    this.router.navigate(['/auth/select-profile']);
  }
}
