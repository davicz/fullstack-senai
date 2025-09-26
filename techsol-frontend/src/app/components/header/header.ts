import { ChangeDetectionStrategy, Component, EventEmitter, Input, Output } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule
  ],
  templateUrl: './header.html',
  styleUrl: './header.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Header {
  @Input() isDarkMode: boolean = true;
  @Input() headerScrolled: boolean = false;
  @Output() toggleTheme = new EventEmitter<void>();
  @Output() loginClicked = new EventEmitter<void>();

  toggleThemeClick() {
    this.toggleTheme.emit();
  }
}
