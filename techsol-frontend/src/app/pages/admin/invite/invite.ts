import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormControl, Validators, ReactiveFormsModule } from '@angular/forms';
import { InviteService } from '../../../services/invite';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-invite',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatProgressSpinnerModule,
    RouterModule
  ],
  templateUrl: './invite.html',
  styleUrl: './invite.scss'
})
export class Invite {
  emailControl = new FormControl('', [Validators.required, Validators.email]);
  isLoading = false;
  feedbackMessage: string | null = null;
  isError = false;

  constructor(private inviteService: InviteService) {}

  onSubmit(): void {
    if (this.emailControl.invalid) {
      return;
    }

    this.isLoading = true;
    this.feedbackMessage = null;
    this.isError = false;

    this.inviteService.sendInvite(this.emailControl.value!).subscribe({
      next: () => {
        this.isLoading = false;
        this.isError = false;
        this.feedbackMessage = `Convite enviado com sucesso para ${this.emailControl.value}!`;
        this.emailControl.reset();
      },
      error: (err) => {
        this.isLoading = false;
        this.isError = true;
        this.feedbackMessage = err.error.message || 'Ocorreu um erro ao enviar o convite.';
      }
    });
  }
}