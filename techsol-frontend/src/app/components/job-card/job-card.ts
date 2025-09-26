import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, Input } from '@angular/core';
import { JobPosition } from '@models/index';


@Component({
  selector: 'app-job-card',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './job-card.html',
  styleUrl: './job-card.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class JobCard {
  jobOpenings: JobPosition[] = [
        { title: 'Senior Backend Engineer - Laravel', location: 'Remoto', type: 'Full-time' },
        { title: 'Frontend Developer - Angular', location: 'Maceió, AL', type: 'Full-time' },
        { title: 'DevOps Specialist', location: 'Remoto', type: 'Full-time' },
    ];
}