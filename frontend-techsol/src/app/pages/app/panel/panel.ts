// src/app/pages/app/panel/panel.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

import { BaseChartDirective } from 'ng2-charts';
import { Chart, registerables, ChartData, ChartOptions, ChartType } from 'chart.js';

// 🔥 Registro obrigatório do Chart.js 4
Chart.register(...registerables);

@Component({
  selector: 'app-panel',
  standalone: true,
  imports: [CommonModule, RouterModule, BaseChartDirective],
  templateUrl: './panel.html',
  styleUrl: './panel.css',
})
export class Panel implements OnInit {

  user: any = null;
  roleSlug = '';

  stats: Array<{ label: string; value: number; icon: string; color: string }> = [];
  shortcuts: Array<{ label: string; route: string; icon: string }> = [];

  // -------------------------
  // LINE CHART
  // -------------------------
  lineChartType: ChartType = 'line';
  lineChartData: ChartData<'line'> = {
    labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
    datasets: [
      {
        data: [50, 120, 180, 240, 300, 420],
        label: 'Usuários cadastrados',
        fill: true,
        tension: 0.35,
        borderWidth: 2,
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37,99,235,0.25)',
        pointBackgroundColor: '#1d4ed8',
        pointRadius: 4
      }
    ]
  };

  lineChartOptions: ChartOptions<'line'> = {
    responsive: true,
    maintainAspectRatio: false
  };

  // -------------------------
  // DOUGHNUT
  // -------------------------
  doughnutType: ChartType = 'doughnut';
  doughnutData: ChartData<'doughnut'> = {
  labels: ['SENAI', 'DRs', 'Unidades', 'Docentes', 'Alunos'],
  datasets: [
    {
      label: 'Quantidade',
      data: [2, 10, 80, 260, 1400],
      backgroundColor: [
        '#2563eb',
        '#16a34a',
        '#ca8a04',
        '#9333ea',
        '#dc2626'
      ]
    }
  ]
  };

  doughnutOptions: ChartOptions<'doughnut'> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  }
};

  // -------------------------
  // BAR
  // -------------------------
  barChartType: ChartType = 'bar';
  barChartData: ChartData<'bar'> = {
    labels: ['AL', 'PE', 'PB', 'BA', 'SE'],
    datasets: [
      {
        label: 'Unidades Operacionais',
        data: [8, 12, 5, 10, 3],
        backgroundColor: '#0ea5e9',
        borderRadius: 6
      }
    ]
  };

  barChartOptions: ChartOptions<'bar'> = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: { grid: { display: false } },
      y: { beginAtZero: true }
    }
  };

  ngOnInit(): void {
    this.loadUser();
    this.loadDashboardData();
  }

  private loadUser() {
    try {
      const raw = localStorage.getItem('siac_user');
      if (raw) {
        this.user = JSON.parse(raw);
        this.roleSlug =
          this.user?.selected_role?.slug ||
          this.user?.roles?.[0]?.slug ||
          '';
      }
    } catch {
      this.user = null;
    }
  }

  private loadDashboardData() {
    if (!this.roleSlug) return;

    if (this.roleSlug === 'national_admin') {
      this.stats = [
        { label: 'Departamentos Regionais', value: 27, icon: '🏢', color: 'blue' },
        { label: 'Unidades Operacionais', value: 550, icon: '🏫', color: 'green' },
        { label: 'Usuários', value: 12000, icon: '👥', color: 'purple' },
      ];

      this.shortcuts = [
        { label: 'Gerenciar Usuários', route: '/app/users', icon: '👥' },
        { label: 'Criar Convite', route: '/app/invites', icon: '✉️' },
      ];
    }
  }
}
