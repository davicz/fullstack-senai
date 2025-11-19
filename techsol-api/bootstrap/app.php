<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // <--- ESSA LINHA É A CHAVE DE TUDO
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Se precisar configurar CORS ou Stateful (Sanctum), é aqui também
        $middleware->statefulApi(); 
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();