<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Determine which paths should be accessible via CORS. Normally, you’ll
    | include all API routes and the Sanctum CSRF cookie route if you use
    | Laravel Sanctum for SPA authentication.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | You can allow all HTTP methods or specify a list of allowed methods.
    |
    */

    'allowed_methods' => ['http://localhost:4200','*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Define which domains can access your API. During development, usually
    | 'http://localhost:4200' is enough for Angular.
    |
    */

    'allowed_origins' => ['http://localhost:4200'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Specify which headers are allowed. Using '*' is fine during development.
    |
    */

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Headers that will be exposed to the browser.
    |
    */

    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | The maximum age (in seconds) of the CORS preflight response.
    |
    */

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Determines whether cookies and authorization headers are allowed.
    |
    */

    'supports_credentials' => true,

];
