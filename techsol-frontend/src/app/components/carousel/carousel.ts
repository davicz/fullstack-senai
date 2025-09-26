import { ChangeDetectionStrategy, signal, Component } from '@angular/core';
import { CommonModule, NgClass, NgStyle } from '@angular/common';

@Component({
  selector: 'app-carousel',
  standalone: true,
  imports: [CommonModule, NgClass, NgStyle],
  templateUrl: './carousel.html',
  styleUrl: './carousel.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Carousel {
  activeSlide = signal(0);
  heroSlides = [
    { title: 'Construa o Futuro. Código a Código.', subtitle: 'Conheça nosso ecossistema de soluções de alta performance que impulsionam os líderes de mercado.', ctaText: 'Explore Nossas Soluções', ctaLink: '#services', bgImage: 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=2070&auto=format&fit=crop' },
    { title: 'Feito por DEVs, para DEVs.', subtitle: 'Junte-se a uma cultura que valoriza o código limpo, a autonomia e a inovação contínua.', ctaText: 'Conheça Nossas Vagas', ctaLink: '#careers', bgImage: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop' },
    { title: 'Soluções de Ponta a Ponta.', subtitle: 'De sistemas embarcados a soluções em nuvem, transformamos desafios complexos em software robusto e escalável.', ctaText: 'Fale com um Especialista', ctaLink: '#contact-form', bgImage: 'https://images.unsplash.com/photo-1587620962725-abab7fe55159?q=80&w=1931&auto=format&fit=crop' },
  ];

  constructor() { 
        setInterval(() => { this.activeSlide.update(current => (current + 1) % this.heroSlides.length); }, 6000);
   }
}
