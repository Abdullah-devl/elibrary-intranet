<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // إذا كان الاستجابة من نوع Response (ليست StreamedResponse مثلاً وقت التحميل)
        if (method_exists($response, 'header')) {
            // منع وضع الموقع داخل إطار iframe لمنع هجمات Clickjacking
            $response->header('X-Frame-Options', 'DENY');
            
            // منع المتصفح من استنتاج نوع الملف Content-Type لتفادي هجمات MIME Sniffing
            $response->header('X-Content-Type-Options', 'nosniff');
            
            // تفعيل حماية المتصفح ضد هجمات Cross-Site Scripting (XSS)
            $response->header('X-XSS-Protection', '1; mode=block');
            
            // منع المصادر الخارجية غير الموثوقة من تحميل ملفات (JavaScript, CSS) - مسموح للـ local فقط
            // هذا يعزز الأمان ضد الـ XSS بشكل جذري
            $response->header('Content-Security-Policy', "default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob:; img-src 'self' data: blob:; media-src 'self' blob:;");
            
            // التحكم في سياسة التوجيه (Referrer Policy)
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        return $response;
    }
}
