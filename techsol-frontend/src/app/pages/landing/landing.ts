import { Component, effect, signal } from '@angular/core';
import { Header } from '../../components/header/header';
import { Footer } from '../../components/footer/footer';
import { Carousel } from '../../components/carousel/carousel';
import { AboutUs } from '../../components/about-us/about-us';
import { ContactForm } from '../../components/contact-form/contact-form';
import { JobCard } from '../../components/job-card/job-card';
import { Router, RouterModule } from '@angular/router';

@Component({
  selector: 'app-landing',
  standalone: true,
  imports: [Carousel, AboutUs, JobCard, ContactForm, RouterModule, Header, Footer],
  templateUrl: './landing.html',
  styleUrl: './landing.css',
})
export class Landing {
   isDarkMode = signal(true);
   headerScrolled = signal(false);

     constructor(private router: Router) {
      effect(() => { document.documentElement.classList.toggle('dark', this.isDarkMode()); });
        window.addEventListener('scroll', () => { this.headerScrolled.set(window.scrollY > 50); });
      }
      toggleTheme() { this.isDarkMode.update(value => !value); }

      onLoginClicked() {
      this.router.navigate(['/login']);
      }

}
