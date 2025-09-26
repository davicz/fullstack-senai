import { ApplicationConfig, importProvidersFrom } from '@angular/core';
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';
import { provideClientHydration } from '@angular/platform-browser';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { provideHttpClient } from '@angular/common/http'; // <-- GARANTE O HTTP
import { ReactiveFormsModule } from '@angular/forms';     // <-- GARANTE OS FORMULÁRIOS
import { provideNgxMask } from 'ngx-mask';              // <-- GARANTE AS MÁSCARAS

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(routes),
    provideClientHydration(),
    provideAnimationsAsync(),
    provideHttpClient(), // PROVIDER GLOBAL DE HTTP
    importProvidersFrom(ReactiveFormsModule), // PROVIDER GLOBAL DE FORMULÁRIOS
    provideNgxMask() // PROVIDER GLOBAL DE MÁSCARAS
  ]
};