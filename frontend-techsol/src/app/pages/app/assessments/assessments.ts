import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AssessmentService } from '../../../services/assessment';
import { Router } from '@angular/router';

@Component({
  selector: 'app-assessments',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './assessments.html',
  styleUrl: './assessments.css'
})
export class Assessments implements OnInit {

  selectedType: 'saep' | 'dn' | 'dr' = 'saep';

  searchTerm = '';
  events: any[] = [];

  loading = false;
  error: string | null = null;

  constructor(
    private api: AssessmentService,
    private router: Router
  ) {}

  ngOnInit() {
    this.loadEvents();
  }

  changeType(type: 'saep' | 'dn' | 'dr') {
    this.selectedType = type;
    this.loadEvents();
  }

  loadEvents() {
    this.loading = true;
    this.error = null;

    const params: any = { type: this.selectedType };

    if (this.searchTerm.trim()) {
      params.search = this.searchTerm;
    }

    this.api.getEvents(params).subscribe({
      next: (events) => {
        this.events = events;
        this.loading = false;
      },
      error: () => {
        this.error = "Erro ao carregar eventos.";
        this.loading = false;
      }
    });
  }

  clearFilters() {
    this.searchTerm = '';
    this.loadEvents();
  }

  openEventDetails(event: any) {
  this.router.navigate(['/app/assessments', event.id]);
    }
}
