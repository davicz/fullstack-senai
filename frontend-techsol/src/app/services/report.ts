// src/app/services/report.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ReportService {
  private apiBase = 'http://localhost/api';

  constructor(private http: HttpClient) {}

  /**
   * Obtém relatório de desempenho por item
   */
  getPerformanceByItem(filters?: any): Observable<any> {
    return this.http.get(`${this.apiBase}/reports/performance-by-item`, { params: filters });
  }

  /**
   * Obtém detalhes completos de uma questão
   */
  getQuestionDetails(questionId: number): Observable<any> {
    return this.http.get(`${this.apiBase}/reports/questions/${questionId}/details`);
  }

  /**
   * Lista anos disponíveis (itinerários)
   */
  getAvailableYears(): number[] {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = currentYear; i >= 2019; i--) {
      years.push(i);
    }
    return years;
  }

  /**
   * Traduz dificuldade
   */
  translateDifficulty(level: string): string {
    const translations: any = {
      'muito_facil': 'Muito Fácil',
      'facil': 'Fácil',
      'medio': 'Médio',
      'dificil': 'Difícil',
      'muito_dificil': 'Muito Difícil'
    };
    return translations[level] || level;
  }

  /**
   * Retorna cor baseada na taxa de acertos
   */
  getAccuracyColor(rate: number): string {
    if (rate >= 75) return 'text-green-600';
    if (rate >= 50) return 'text-blue-600';
    if (rate >= 25) return 'text-yellow-600';
    return 'text-red-600';
  }

  /**
   * Retorna cor baseada na dificuldade
   */
  getDifficultyColor(difficulty: string): string {
    const colors: any = {
      'Muito Fácil': 'bg-green-100 text-green-800',
      'Fácil': 'bg-blue-100 text-blue-800',
      'Médio': 'bg-yellow-100 text-yellow-800',
      'Difícil': 'bg-orange-100 text-orange-800',
      'Muito Difícil': 'bg-red-100 text-red-800'
    };
    return colors[difficulty] || 'bg-gray-100 text-gray-800';
  }
}