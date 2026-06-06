<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المشرف</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ $settings->color_primary }}',
                        accent: '{{ $settings->color_accent }}',
                        bglight: '{{ $settings->color_bglight }}'
                    },
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, {{ $settings->color_primary }} 0%, #000000 100%);
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white/95 backdrop-blur-md rounded-2xl p-8 shadow-2xl border border-white/20">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl text-primary">
                @if($settings->logo_type === 'image' && $settings->logo_path)
                    <img src="{{ asset($settings->logo_path) }}" alt="Logo" class="h-10 w-auto object-contain">
                @else
                    <i class="{{ $settings->logo_icon }}"></i>
                @endif
            </div>
            <h1 class="text-xl font-bold text-slate-800">{{ $settings->name }}</h1>
            <p class="text-xs text-slate-400 mt-1">صفحة الدخول الآمن للوحة الإعدادات</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('error'))
            <div class="mb-5 p-3.5 bg-rose-50 border-r-4 border-rose-500 rounded-lg text-xs font-semibold text-rose-800 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('settings.login_submit') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-slate-600 mb-2">البريد الالكتروني</label>
                <div class="relative">
                    <input type="email" name="email" id="email" required autofocus placeholder="admin@gmail.com"
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none transition duration-200 text-center font-mono">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-600 mb-2">كلمة المرور</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none transition duration-200 text-center font-mono">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-primary hover:opacity-95 text-white font-bold rounded-xl transition duration-300 shadow-md flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> تسجيل الدخول
            </button>
        </form>

        <!-- Back Link -->
        <div class="text-center mt-6 pt-5 border-t border-slate-100">
            <a href="{{ url('/library') }}" class="text-xs font-bold text-slate-400 hover:text-primary transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i> العودة للمكتبة الرئيسية
            </a>
        </div>

    </div>

</body>
</html>
