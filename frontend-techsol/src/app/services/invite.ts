// src/app/services/invite.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class InviteService {
  private apiBase = 'http://localhost/api';

  constructor(private http: HttpClient) {}

  // GET /operational-units
  getOperationalUnits(): Observable<any> {
    return this.http.get(`${this.apiBase}/operational-units`);
  }

  // GET /regional-departments
  getRegionalDepartments(): Observable<any> {
    return this.http.get(`${this.apiBase}/regional-departments`);
  }

    // GET /invites (Lista os convites enviados)
  getInvites(): Observable<any> {
      return this.http.get(`${this.apiBase}/invites`);
  }

  // POST /invites/start
  startInvite(payload: { email: string; cpf: string }): Observable<any> {
    return this.http.post(`${this.apiBase}/invites/start`, payload);
  }

  // POST /invites/{invitation}/roles
  assignRoles(invitationId: number, roles: number[]): Observable<any> {
    return this.http.post(`${this.apiBase}/invites/${invitationId}/roles`, { roles });
  }

  // POST /invites/{invitation}/context
  assignContext(invitationId: number, assignments: any[]): Observable<any> {
    return this.http.post(`${this.apiBase}/invites/${invitationId}/context`, { assignments });
  }

  // POST /invites/{invitation}/send
  sendInvite(invitationId: number): Observable<any> {
    return this.http.post(`${this.apiBase}/invites/${invitationId}/send`, {});
  }

  // CANCELAR CONVITE
cancelInvite(id: number) {
  // Ajuste a URL conforme sua rota no Laravel (ex: /invites, /invitations, etc)
  return this.http.patch(`${this.apiBase}/invites/${id}/cancel`, {});
}
}
