<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthMiddleware
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
        // التحقق من أن المشرف قد سجل دخوله
        if (!session('admin_authenticated')) {
            // إذا كان الطلب من نوع AJAX أو JSON، نرجع خطأ 401
            if ($request->expectsJson()) {
                return response()->json(['error' => 'غير مصرح لك بالوصول. يرجى تسجيل الدخول.'], 401);
            }
            // غير ذلك نعيده إلى صفحة الدخول
            return redirect()->route('settings.login')->with('error', 'يجب تسجيل الدخول كمسؤول للوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}
