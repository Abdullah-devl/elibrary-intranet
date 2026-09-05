<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك في {{ $settings->name }}</title>
    
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
            background-color: {{ $settings->color_bglight }};
            background-image: radial-gradient(circle at 10% 20%, rgba(240, 244, 248, 0.5) 0%, rgba(254, 254, 254, 0.5) 90.1%);
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, {{ $settings->color_primary }} 0%, rgba(15, 23, 42, 0.95) 100%);
        }
        
        .animated-icon {
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .glow-effect:hover {
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Header / Nav (مبسط) -->
    <header class="w-full max-w-7xl mx-auto px-6 py-5 flex justify-between items-center z-10">
        <div class="text-xl font-bold flex items-center gap-3 truncate max-w-[60%]">
            @if(($settings->org_logo_type ?? 'icon') === 'image' && !empty($settings->org_logo_path))
                <img src="{{ asset($settings->org_logo_path) }}" alt="Organization Logo" class="h-9 w-auto object-contain flex-shrink-0">
            @else
                <i class="{{ $settings->org_logo_icon ?? 'fa-solid fa-building' }} text-primary text-2xl flex-shrink-0"></i>
            @endif
            <span class="text-slate-800 font-extrabold truncate">{{ $settings->name }}</span>
        </div>
        <div class="flex items-center gap-4 flex-shrink-0">
            <span class="text-xs bg-emerald-50 text-emerald-700 p-2 sm:px-3 sm:py-1.5 rounded-full font-bold flex items-center gap-1.5 border border-emerald-100" title="الشبكة المحلية نشطة">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="hidden sm:inline">الشبكة المحلية نشطة</span>
            </span>
        </div>
    </header>

    <!-- Main Section -->
    <main class="flex-grow flex items-center justify-center py-10 px-6">
        <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- النص والترحيب (اليمين) -->
            <div class="lg:col-span-7 space-y-8 text-right">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary/10 text-primary rounded-xl text-sm font-bold border border-primary/5">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>المكتبة التعليمية الشاملة</span>
                </div>
                
                <h1 class="text-4xl lg:text-5xl font-extrabold text-slate-900 leading-normal">
                    مرحباً بك في
                    <span class="block mt-3 pb-2 text-transparent bg-clip-text bg-gradient-to-l from-primary to-accent">{{ $settings->name }}</span>
                </h1>
                
                <p class="text-lg text-slate-600 leading-relaxed max-w-xl">
                    {{ $settings->welcome_text }}
                </p>

                <!-- زر التصفح الرئيسي التفاعلي -->
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('library.index') }}" class="px-8 py-4 bg-primary hover:opacity-95 text-white font-extrabold rounded-2xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:shadow-primary/25 flex items-center gap-3 text-lg group">
                        <span>ابدأ تصفح المكتبة</span>
                        <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition duration-300"></i>
                    </a>
                </div>
            </div>

            <!-- الشعار التفاعلي الكبير (اليسار) -->
            <div class="lg:col-span-5 flex justify-center items-center">
                @if($settings->logo_type === 'image' && $settings->logo_path)
                    <div class="relative group p-6 bg-white/40 backdrop-blur-sm rounded-3xl border border-white/20 shadow-xl transition duration-500 hover:shadow-2xl">
                        <img src="{{ asset($settings->logo_path) }}" alt="Logo" class="max-h-64 md:max-h-72 w-auto object-contain animated-icon filter drop-shadow-2xl">
                    </div>
                @else
                    <div class="w-56 h-56 md:w-64 md:h-64 rounded-full bg-primary/5 border-4 border-dashed border-primary/20 flex items-center justify-center animated-icon shadow-inner relative group transition duration-500 hover:border-accent/40">
                        <i class="{{ $settings->logo_icon }} text-8xl md:text-9xl text-primary drop-shadow-[0_10px_15px_rgba(30,41,59,0.15)] group-hover:scale-110 transition duration-300"></i>
                    </div>
                @endif
            </div>

        </div>
    </main>

    <!-- Footer -->
    @include('components.footer')

</body>
</html>
