import { Component, OnInit } from '@angular/core';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { CommonModule } from '@angular/common';
import { InviteDialog } from '../invite-dialog/invite-dialog';
import { UserDetailedDialog } from '../user-detailed-dialog/user-detailed-dialog';
import { User } from '@models/index';

@Component({
  standalone: true,
  selector: 'app-user-list',
  imports: [
    CommonModule,
    MatButtonModule,
    MatDialogModule,
  ],
  templateUrl: './user-list.html',
  styleUrl: './user-list.css'
})
export class UserList implements OnInit {
  users: User[] = [];

   constructor(public dialog: MatDialog) {}
  
  ngOnInit(): void {
    this.fetchUsers();
  }

  fetchUsers(): void {
    // Aqui você chamará o UserService para buscar os dados.
    // Por enquanto, podemos usar dados mocados:
    this.users = [
      { id: 1, name: 'Ana Carolina', email: 'ana.carolina@email.com', cpf:'11133322285', profile: 'Gente e Cultura', status: 'Finalizado' },
      { id: 2, name: 'Bruno Alves', email: 'bruno.alves@email.com', cpf:'11133322285', profile: 'Colaborador Comum', status: 'Finalizado' },
      { id: 3, name: 'Carlos de Souza', email: 'carlos.souza@email.com', cpf:'11133322285', profile: 'Administrador', status: 'Finalizado' },
    ];
  }
  // Métodos para os botões que ainda serão implementados
 openInviteDialog(): void {
    const dialogRef = this.dialog.open(InviteDialog, {
      width: '450px',
      // panelClass: 'techsol-dialog' // Opcional: classe CSS customizada para o painel
    });

    dialogRef.afterClosed().subscribe(result => {
      // O 'result' será 'true' se o convite foi enviado com sucesso
      if (result) {
        console.log('Convite enviado! Atualizar a lista ou mostrar notificação.');
        // this.fetchUsers(); // Você pode querer recarregar a lista
      }
    });
  }

  exportToExcel(): void {
    console.log('Exportando dados...');
  }

  viewUserDetails(user: User): void {
    const dialogRef = this.dialog.open(UserDetailedDialog, {
      width: '600px',
      data: user // Passando o objeto 'user' completo para o modal
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result) {
        console.log('Dados do usuário atualizados. Recarregando lista...');
        this.fetchUsers();
      }
    });
  }
}
