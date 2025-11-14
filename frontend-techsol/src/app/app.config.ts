// src/app/app.config.ts
import { ApplicationConfig, importProvidersFrom } from '@angular/core'; // REMOVIDO 'provideZonelessChangeDetection'
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';
import { provideHttpClient, withFetch, withInterceptors } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { provideAnimations } from '@angular/platform-browser/animations';
import { authInterceptor } from './interceptors/auth';

export const appConfig: ApplicationConfig = {
  providers: [
    // provideZonelessChangeDetection(), // <-- LINHA REMOVIDA
    provideRouter(routes),
    provideHttpClient(
      withFetch(),
      withInterceptors([authInterceptor])
    ),
    importProvidersFrom(FormsModule),
    provideAnimations()
  ]
};