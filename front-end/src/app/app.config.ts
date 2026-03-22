import { ApplicationConfig, provideBrowserGlobalErrorListeners } from '@angular/core';
import { provideRouter, withComponentInputBinding }               from '@angular/router';
import { provideHttpClient, withInterceptors }                    from '@angular/common/http';

import { routes }          from './app.routes';
import { authInterceptor } from './core/auth/auth.interceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    // Keep the original CLI provider — do not remove
    provideBrowserGlobalErrorListeners(),

    // Router with @Input() binding for route params
    provideRouter(routes, withComponentInputBinding()),

    // HTTP client + auth interceptor
    // Automatically adds: Authorization: Bearer <token> to every request
    provideHttpClient(
      withInterceptors([authInterceptor])
    ),

  ],
};