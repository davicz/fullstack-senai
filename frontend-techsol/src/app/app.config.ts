import { ApplicationConfig, importProvidersFrom, provideZonelessChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';
import { provideHttpClient, withFetch, withInterceptors } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { provideAnimations } from '@angular/platform-browser/animations';
import { authInterceptor } from './interceptors/auth';

export const appConfig: ApplicationConfig = {
  providers: [
    provideZonelessChangeDetection(), 
    provideRouter(routes),
    provideHttpClient(
      withFetch(),
      withInterceptors([authInterceptor]) // 👈 Interceptor adicionado aqui!
    ),
    importProvidersFrom(FormsModule),
    provideAnimations()
  ]
};
