import { bootstrapApplication } from '@angular/platform-browser';
import { appConfig } from './app/app.config';
import { App } from './app/app'; // Usa o nome da classe 'App'

bootstrapApplication(App, appConfig)
  .catch((err) => console.error(err));