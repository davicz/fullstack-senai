import { ApplicationConfig, importProvidersFrom, provideZonelessChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';
import { provideHttpClient, withFetch } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { provideAnimations } from '@angular/platform-browser/animations';

export const appConfig: ApplicationConfig = {
  providers: [
    // Habilita o modo Zoneless (como o Guilherme configurou)
    provideZonelessChangeDetection(), 

    provideRouter(routes),
    provideHttpClient(withFetch()), // Para chamadas de API
    importProvidersFrom(FormsModule), // Para formulários (ngModel)
    provideAnimations() // Para animações do Angular
  ]
};