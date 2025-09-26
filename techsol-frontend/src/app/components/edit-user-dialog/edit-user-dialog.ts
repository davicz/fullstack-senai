import { Component, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  Validators,
  ReactiveFormsModule,
} from '@angular/forms';
import {
  MAT_DIALOG_DATA,
  MatDialogRef,
  MatDialogModule,
} from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { CollaboratorService } from '../../services/collaborator';

@Component({
  selector: 'app-edit-user-dialog',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatProgressSpinnerModule,
  ],
  templateUrl: './edit-user-dialog.html',
  styleUrl: './edit-user-dialog.scss',
})
export class EditUserDialog {
  editForm: FormGroup;
  isLoading = false;
  // Simulação dos perfis que podemos selecionar
  roles = [
    { id: 1, name: 'Administrador' },
    { id: 2, name: 'Gente e Cultura' },
    { id: 3, name: 'Colaborador Comum' },
  ];

  constructor(
    public dialogRef: MatDialogRef<EditUserDialog>,
    @Inject(MAT_DIALOG_DATA) public data: any,
    private fb: FormBuilder,
    private collaboratorService: CollaboratorService
  ) {
    this.editForm = this.fb.group({
      role_id: [data.user.role_id, Validators.required],
      password: [''], // Senha é opcional
    });
  }

  onCancel(): void {
    this.dialogRef.close();
  }

  onSave(): void {
    if (this.editForm.invalid) {
      return;
    }
    this.isLoading = true;

    // Chamar o serviço para atualizar o usuário
    this.collaboratorService
      .updateCollaborator(this.data.user.id, this.editForm.value)
      .subscribe({
        next: () => {
          this.isLoading = false;
          this.dialogRef.close(true); // Fecha o dialog e retorna 'true' para indicar sucesso
        },
        error: (err) => {
          console.error('Erro ao atualizar usuário', err);
          this.isLoading = false;
          // Aqui poderíamos mostrar uma mensagem de erro
        },
      });
  }
}
