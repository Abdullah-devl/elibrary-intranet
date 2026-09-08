<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        // تطبيق ترويسات الحماية (Security Headers) على جميع المسارات بشكل افتراضي
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
        
        // تسجيل الـ Middleware الخاص بصلاحيات المشرف لتطبيقها على مسارات الإدارة
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
