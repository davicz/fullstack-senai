import { Component, Inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms'; // Importe ReactiveFormsModule
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog'; // Importe MatDialogModule
import { MatFormFieldModule } from '@angular/material/form-field'; // Importe MatFormFieldModule
import { MatInputModule } from '@angular/material/input';       // Importe MatInputModule
import { MatSelectModule } from '@angular/material/select';       // Importe MatSelectModule
import { MatButtonModule } from '@angular/material/button';       // Importe MatButtonModule
import { CommonModule } from '@angular/common';  
import { User } from '@models/index';
import { AuthService } from '../../../../services/auth';

@Component({
  selector: 'app-user-detailed-dialog',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule
  ],
  templateUrl: './user-detailed-dialog.html',
  styleUrl: './user-detailed-dialog.css'
})
export class UserDetailedDialog implements OnInit {
  editProfileForm!: FormGroup;
  loggedInUserRole: 'Administrador' | 'Gente e Cultura' | 'Colaborador Comum';
  showPasswordField = false;

  constructor(
    private fb: FormBuilder,
    public dialogRef: MatDialogRef<UserDetailedDialog>,
    @Inject(MAT_DIALOG_DATA) public user: User,
    // private authService: AuthService // Injete seu serviço de autenticação
  ) {
    // SIMULAÇÃO: Obter o perfil do usuário logado. Na aplicação real, viria de um AuthService.
    this.loggedInUserRole = 'Administrador'; // Mude para 'Gente e Cultura' para testar
  }

  ngOnInit(): void {
    this.editProfileForm = this.fb.group({
      profile: [this.user.profile, Validators.required],
      password: ['']
    });

    this.checkPasswordFieldVisibility(this.user.profile);

    // Escuta mudanças no select de perfil para mostrar/ocultar o campo de senha
    this.editProfileForm.get('profile')?.valueChanges.subscribe(value => {
      this.checkPasswordFieldVisibility(value);
    });
  }

  checkPasswordFieldVisibility(profile: string): void {
    this.showPasswordField = profile === 'Administrador' || profile === 'Gente e Cultura';
  }

  onCancel(): void {
    this.dialogRef.close();
  }

  onSave(): void {
    if (this.editProfileForm.valid) {
      const { profile, password } = this.editProfileForm.value;
      console.log(`Salvando para usuário ID ${this.user.id}:`, { profile, password });

      // LÓGICA REAL:
      // this.userService.updateUserProfile(this.user.id, profile, password).subscribe({
      //   next: () => this.dialogRef.close(true),
      //   error: (err) => console.error('Erro ao salvar', err)
      // });
      this.dialogRef.close(true); // Simulando sucesso
    }
  }
}
