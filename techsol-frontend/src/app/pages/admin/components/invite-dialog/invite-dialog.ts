import { Component } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-invite-dialog',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule
  ],
  templateUrl: './invite-dialog.html',
  styleUrl: './invite-dialog.css'
})
export class InviteDialog {
   inviteForm: FormGroup;

  constructor(
    private fb: FormBuilder,
    public dialogRef: MatDialogRef<InviteDialog>,
    // private userService: UserService // Descomente quando o serviço existir
  ) {
    this.inviteForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });
  }

  get email() {
    return this.inviteForm.get('email');
  }

  onCancel(): void {
    this.dialogRef.close();
  }

  onConfirm(): void {
    if (this.inviteForm.valid) {
      console.log('Enviando convite para:', this.email?.value);
      // LÓGICA REAL:
      // this.userService.inviteUser(this.email?.value).subscribe({
      //   next: () => this.dialogRef.close(true), // Fecha e retorna sucesso
      //   error: (err) => console.error('Erro ao enviar convite', err)
      // });
      this.dialogRef.close(true); // Simulando sucesso por enquanto
    }
  }
}
