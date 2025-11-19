// src/app/services/competency.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class CompetencyService {
  private apiBase = 'http://localhost/api';

  constructor(private http: HttpClient) {}

  // ==========================================
  // CRUD de Competências
  // ==========================================

  /**
   * Lista todas as competências
   * @param filters - Filtros opcionais (course_id, level, active_only, root_only)
   */
  getCompetencies(filters?: any): Observable<any> {
    return this.http.get(`${this.apiBase}/competencies`, { params: filters });
  }

  /**
   * Busca uma competência específica
   */
  getCompetency(id: number): Observable<any> {
    return this.http.get(`${this.apiBase}/competencies/${id}`);
  }

  /**
   * Cria uma nova competência (apenas admins)
   */
  createCompetency(data: any): Observable<any> {
    return this.http.post(`${this.apiBase}/competencies`, data);
  }

  /**
   * Atualiza uma competência (apenas admins)
   */
  updateCompetency(id: number, data: any): Observable<any> {
    return this.http.put(`${this.apiBase}/competencies/${id}`, data);
  }

  /**
   * Exclui uma competência (apenas admins)
   */
  deleteCompetency(id: number): Observable<any> {
    return this.http.delete(`${this.apiBase}/competencies/${id}`);
  }

  // ==========================================
  // Vinculação com Cursos
  // ==========================================

  /**
   * Vincula uma competência a um curso
   */
  attachToCourse(competencyId: number, data: any): Observable<any> {
    return this.http.post(`${this.apiBase}/competencies/${competencyId}/courses`, data);
  }

  // ==========================================
  // Progresso do Aluno
  // ==========================================

  /**
   * Obtém o progresso de um aluno em competências
   * @param userId - ID do usuário (opcional, usa o logado se não informar)
   * @param filters - Filtros opcionais (course_id, proficiency_level)
   */
  getStudentProgress(userId?: number, filters?: any): Observable<any> {
    const url = userId 
      ? `${this.apiBase}/users/${userId}/competencies/progress`
      : `${this.apiBase}/users/competencies/progress`;
    
    return this.http.get(url, { params: filters });
  }

  /**
   * Obtém o ranking de alunos em uma competência
   */
  getCompetencyRanking(competencyId: number, filters?: any): Observable<any> {
    return this.http.get(`${this.apiBase}/competencies/${competencyId}/ranking`, { params: filters });
  }

  // ==========================================
  // Helpers
  // ==========================================

  /**
   * Formata o nível de proficiência para exibição
   */
  formatProficiencyLevel(level: string): string {
    const labels: any = {
      'not_started': 'Não iniciado',
      'developing': 'Em desenvolvimento',
      'proficient': 'Proficiente',
      'advanced': 'Avançado',
      'expert': 'Expert'
    };
    return labels[level] || level;
  }

  /**
   * Retorna a cor baseada no nível de proficiência
   */
  getProficiencyColor(level: string): string {
    const colors: any = {
      'not_started': 'gray',
      'developing': 'yellow',
      'proficient': 'blue',
      'advanced': 'green',
      'expert': 'purple'
    };
    return colors[level] || 'gray';
  }

  /**
   * Retorna a cor baseada na pontuação (0-100)
   */
  getScoreColor(score: number): string {
    if (score < 40) return 'red';
    if (score < 60) return 'yellow';
    if (score < 80) return 'blue';
    return 'green';
  }
}