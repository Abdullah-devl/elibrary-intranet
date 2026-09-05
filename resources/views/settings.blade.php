<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات المكتبة الإلكترونية</title>
    
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
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-bglight text-slate-800 antialiased min-h-screen pb-12">

    <!-- Navbar -->
    <nav class="bg-primary text-white shadow-lg transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <div class="text-xl sm:text-2xl font-bold flex items-center gap-2 sm:gap-3 truncate max-w-[50%]">
                @if($settings->logo_type === 'image' && $settings->logo_path)
                    <img id="nav-logo-img" src="{{ asset($settings->logo_path) }}" alt="Logo" class="h-8 sm:h-9 w-auto object-contain flex-shrink-0">
                @else
                    <i id="nav-logo-icon" class="{{ $settings->logo_icon }} text-accent flex-shrink-0"></i>
                @endif
                <span id="nav-title" class="truncate">{{ $settings->name }}</span>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                <a href="{{ url('/library') }}" class="bg-white/10 hover:bg-white/20 text-white p-2 sm:px-4 sm:py-2 rounded-lg transition duration-300 text-sm flex items-center gap-2" title="العودة للمكتبة">
                    <i class="fa-solid fa-arrow-left"></i> <span class="hidden sm:inline">العودة للمكتبة</span>
                </a>
                <form action="{{ route('settings.logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white p-2 sm:px-3 sm:py-2 rounded-lg transition duration-300 text-sm flex items-center gap-2 font-bold" title="تسجيل الخروج">
                        <i class="fa-solid fa-right-from-bracket"></i> <span class="hidden sm:inline">تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 mt-8">
        
        <!-- Success Alert -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-r-4 border-emerald-500 rounded-lg shadow-sm text-emerald-800 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- لوحة الإحصائيات العامة للمكتبة -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-8 space-y-6">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-4">
                <i class="fa-solid fa-chart-pie text-accent"></i>
                إحصائيات المكتبة الإلكترونية
            </h2>

            <!-- بطاقات الإحصائيات -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- بطاقة الزيارات -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 border border-indigo-100 rounded-2xl p-6 flex items-center justify-between shadow-sm hover:shadow transition duration-200">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-indigo-500 uppercase tracking-wider">إجمالي الزيارات</span>
                        <h3 class="text-3xl font-extrabold text-indigo-900">{{ number_format($totalVisits ?? 0) }}</h3>
                        <p class="text-xs text-indigo-600/80 font-semibold">إجمالي مرات فتح وتصفح أقسام المكتبة</p>
                    </div>
                    <div class="bg-indigo-500 text-white w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-md shadow-indigo-500/20">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                </div>

                <!-- بطاقة التحميلات والمشاهدات -->
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 border border-emerald-100 rounded-2xl p-6 flex items-center justify-between shadow-sm hover:shadow transition duration-200">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">التحميلات والمشاهدات</span>
                        <h3 class="text-3xl font-extrabold text-emerald-900">{{ number_format($totalDownloads ?? 0) }}</h3>
                        <p class="text-xs text-emerald-600/80 font-semibold">إجمالي تشغيل الملفات أو تنزيلها مباشرة</p>
                    </div>
                    <div class="bg-emerald-500 text-white w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-md shadow-emerald-500/20">
                        <i class="fa-solid fa-download"></i>
                    </div>
                </div>

                <!-- بطاقة النسخ للفلاش ميموري -->
                <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 border border-amber-100 rounded-2xl p-6 flex items-center justify-between shadow-sm hover:shadow transition duration-200">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">النسخ للفلاش ميموري</span>
                        <h3 class="text-3xl font-extrabold text-amber-900">{{ number_format($totalCopies ?? 0) }}</h3>
                        <p class="text-xs text-amber-600/80 font-semibold">إجمالي الملفات المنسوخة مباشرة للفلاش</p>
                    </div>
                    <div class="bg-amber-500 text-white w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-md shadow-amber-500/20">
                        <i class="fa-solid fa-copy"></i>
                    </div>
                </div>
            </div>

            <!-- جداول الإحصائيات التفصيلية -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-2">
                <!-- جدول الأكثر تحميلاً -->
                <div class="lg:col-span-2 space-y-3">
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-fire text-rose-500"></i>
                        أعلى 10 ملفات طلباً (تحميل ومشاهدة ونسخ للفلاش)
                    </h3>
                    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-right text-xs">
                                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-100">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4">اسم الملف</th>
                                        <th class="py-3 px-4">القسم</th>
                                        <th class="py-3 px-4 text-center">عدد الطلبات</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @forelse($topDownloads ?? [] as $index => $item)
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                            <td class="py-3 px-4 font-semibold text-slate-800 max-w-xs truncate" title="{{ $item->file_name }}">
                                                {{ $item->file_name }}
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                                    {{ $categoryNames[$item->category_id] ?? $item->category_id }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-center font-bold text-accent">{{ number_format($item->total_count) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-8 text-center text-slate-400 font-semibold">
                                                <i class="fa-solid fa-inbox text-2xl mb-2 block"></i>
                                                لا توجد بيانات تحميلات مسجلة بعد.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- جدول إحصائيات الأقسام -->
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-chart-bar text-indigo-500"></i>
                        الزيارات حسب الأقسام
                    </h3>
                    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">القسم</th>
                                    <th class="py-3 px-4 text-center">الزيارات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($visitsByCategory ?? [] as $item)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3 px-4 font-semibold text-slate-800">
                                            {{ $categoryNames[$item->category_id] ?? $item->category_id }}
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-indigo-600">{{ number_format($item->total_visits) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-8 text-center text-slate-400 font-semibold">
                                            <i class="fa-solid fa-inbox text-2xl mb-2 block"></i>
                                            لا توجد بيانات زيارات مسجلة بعد.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- ================= القسم العلوي (كامل عرض الشاشة) ================= -->
            <div class="space-y-6">
                <!-- كارت معلومات المكتبة الأساسية والشعار والمسارات والأمان -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-6">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-4">
                        <i class="fa-solid fa-sliders text-accent"></i>
                        تعديل إعدادات الهوية والمسارات والأمان للمكتبة
                    </h2>

                    <!-- اسم المكتبة والنص الترحيبي -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">اسم المكتبة الإلكترونية</label>
                            <input type="text" name="name" id="name" value="{{ $settings->name }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none transition duration-200">
                        </div>
                        <div>
                            <label for="welcome_text" class="block text-sm font-semibold text-slate-700 mb-2">النص الترحيبي لزوار المكتبة (الصفحة الرئيسية)</label>
                            <textarea name="welcome_text" id="welcome_text" rows="2" required
                                class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none transition duration-200 text-sm">{{ $settings->welcome_text ?? '' }}</textarea>
                        </div>
                    </div>

                    <!-- الانتقال لإدارة الأقسام -->
                    <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 font-sans">
                        <div>
                            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm sm:text-base">
                                <i class="fa-solid fa-folder-tree text-accent"></i> إدارة أقسام المكتبة الرقمية
                            </h3>
                            <p class="text-xs text-slate-500 mt-1 font-semibold">
                                يمكنك إدارة وإضافة وحذف الأقسام وتحديد الصيغ والامتدادات المسموح بها عبر صفحة مخصصة ومستقلة.
                            </p>
                        </div>
                        <a href="{{ route('settings.categories.index') }}" class="w-full sm:w-auto px-5 py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm text-center">
                            <i class="fa-solid fa-sliders text-accent"></i> الانتقال لصفحة إدارة الأقسام
                        </a>
                    </div>

                    <!-- حماية حساب المشرف -->
                    <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                        <h3 class="font-semibold text-slate-800 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-accent"></i> حماية حساب المشرف
                        </h3>
                        <div class="text-xs text-slate-400 mb-4 bg-slate-100/50 p-2.5 rounded-lg">
                            يمكنك تعديل البريد الإلكتروني للمشرف بحرية. لتعديل كلمة المرور، اكتب كلمة المرور الحالية ثم الجديدة وتأكيدها (أو اترك حقول كلمة المرور فارغة للاحتفاظ بكلمة المرور الحالية).
                        </div>
                        
                        <div class="space-y-4 mb-4">
                            <div>
                                <label for="admin_email" class="block text-xs font-semibold text-slate-600 mb-1.5">البريد الإلكتروني للمشرف</label>
                                <input type="email" name="admin_email" id="admin_email" value="{{ $settings->admin_email ?? 'admin@gmail.com' }}" required
                                    class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none text-xs transition duration-200 font-mono">
                                @error('admin_email')
                                    <p class="text-rose-500 text-xs mt-1 font-semibold"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-xs font-semibold text-slate-600 mb-1.5">كلمة المرور الحالية</label>
                                <input type="password" name="current_password" id="current_password" autocomplete="current-password" placeholder="••••••••"
                                    class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none text-xs transition duration-200 font-mono">
                                @error('current_password')
                                    <p class="text-rose-500 text-xs mt-1 font-semibold"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_password" class="block text-xs font-semibold text-slate-600 mb-1.5">كلمة المرور الجديدة</label>
                                    <input type="password" name="new_password" id="new_password" autocomplete="new-password" placeholder="••••••••"
                                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none text-xs transition duration-200 font-mono">
                                    @error('new_password')
                                        <p class="text-rose-500 text-xs mt-1 font-semibold"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                                @enderror
                                </div>
                                <div>
                                    <label for="new_password_confirmation" class="block text-xs font-semibold text-slate-600 mb-1.5">تأكيد كلمة المرور الجديدة</label>
                                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" autocomplete="new-password" placeholder="••••••••"
                                        class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none text-xs transition duration-200 font-mono">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- شعار الجهة / المؤسسة -->
                    <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-building text-accent"></i> شعار الجهة (للصفحة الترحيبية الرئيسية)
                        </h3>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <label class="border-2 border-slate-200 rounded-xl p-4 flex items-center gap-3 cursor-pointer hover:border-accent transition duration-200">
                                <input type="radio" name="org_logo_type" value="icon" class="text-accent focus:ring-accent" {{ ($settings->org_logo_type ?? 'icon') == 'icon' ? 'checked' : '' }} onchange="toggleOrgLogo(this.value)">
                                <div>
                                    <div class="font-bold text-sm text-slate-700">أيقونة جاهزة</div>
                                    <div class="text-xs text-slate-400">اختر رمز FontAwesome</div>
                                </div>
                            </label>
                            
                            <label class="border-2 border-slate-200 rounded-xl p-4 flex items-center gap-3 cursor-pointer hover:border-accent transition duration-200">
                                <input type="radio" name="org_logo_type" value="image" class="text-accent focus:ring-accent" {{ ($settings->org_logo_type ?? 'icon') == 'image' ? 'checked' : '' }} onchange="toggleOrgLogo(this.value)">
                                <div>
                                    <div class="font-bold text-sm text-slate-700">صورة مخصصة</div>
                                    <div class="text-xs text-slate-400">ارفع شعار خاص بالجهة</div>
                                </div>
                            </label>
                        </div>

                        <!-- Icon Input -->
                        <div id="org-icon-input-group" class="{{ ($settings->org_logo_type ?? 'icon') == 'image' ? 'hidden' : '' }} space-y-3">
                            <label for="org_logo_icon" class="block text-sm font-semibold text-slate-700">رمز الأيقونة (FontAwesome class)</label>
                            <div class="relative">
                                <input type="text" name="org_logo_icon" id="org_logo_icon" value="{{ $settings->org_logo_icon ?? 'fa-solid fa-building' }}"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none transition duration-200">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">
                                    <i id="org-logo-icon-indicator" class="{{ $settings->org_logo_icon ?? 'fa-solid fa-building' }}"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Image Input -->
                        <div id="org-image-input-group" class="{{ ($settings->org_logo_type ?? 'icon') == 'icon' ? 'hidden' : '' }} space-y-3">
                            <label class="block text-sm font-semibold text-slate-700">ملف الشعار (صورة)</label>
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-accent transition duration-200 relative cursor-pointer">
                                <input type="file" name="org_logo_file" id="org_logo_file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                                <p class="text-sm font-semibold text-slate-600">اضغط لرفع ملف أو اسحبه هنا</p>
                                <p class="text-xs text-slate-400 mt-1">يدعم PNG, JPG, SVG</p>
                            </div>
                        </div>
                    </div>

                    <!-- شعار المكتبة -->
                    <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-image text-accent"></i> شعار المكتبة
                        </h3>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <label class="border-2 border-slate-200 rounded-xl p-4 flex items-center gap-3 cursor-pointer hover:border-accent transition duration-200" id="label-logo-icon">
                                <input type="radio" name="logo_type" value="icon" class="text-accent focus:ring-accent" {{ $settings->logo_type == 'icon' ? 'checked' : '' }}>
                                <div>
                                    <div class="font-bold text-sm text-slate-700">أيقونة جاهزة</div>
                                    <div class="text-xs text-slate-400">اختر رمز FontAwesome</div>
                                </div>
                            </label>
                            
                            <label class="border-2 border-slate-200 rounded-xl p-4 flex items-center gap-3 cursor-pointer hover:border-accent transition duration-200" id="label-logo-image">
                                <input type="radio" name="logo_type" value="image" class="text-accent focus:ring-accent" {{ $settings->logo_type == 'image' ? 'checked' : '' }}>
                                <div>
                                    <div class="font-bold text-sm text-slate-700">صورة مخصصة</div>
                                    <div class="text-xs text-slate-400">ارفع شعار خاص بالمكتبة</div>
                                </div>
                            </label>
                        </div>

                        <!-- Icon Input -->
                        <div id="icon-input-group" class="{{ $settings->logo_type == 'image' ? 'hidden' : '' }} space-y-3">
                            <label for="logo_icon" class="block text-sm font-semibold text-slate-700">رمز الأيقونة (FontAwesome class)</label>
                            <div class="relative">
                                <input type="text" name="logo_icon" id="logo_icon" value="{{ $settings->logo_icon ?: 'fa-solid fa-graduation-cap' }}"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none transition duration-200">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">
                                    <i id="logo-icon-indicator" class="{{ $settings->logo_icon ?: 'fa-solid fa-graduation-cap' }}"></i>
                                </div>
                            </div>
                            <div class="text-xs text-slate-400">أمثلة: <code class="bg-slate-100 px-1 py-0.5 rounded">fa-solid fa-graduation-cap</code>، <code class="bg-slate-100 px-1 py-0.5 rounded">fa-solid fa-book-open</code>، <code class="bg-slate-100 px-1 py-0.5 rounded">fa-solid fa-school</code></div>
                        </div>

                        <!-- Image Input -->
                        <div id="image-input-group" class="{{ $settings->logo_type == 'icon' ? 'hidden' : '' }} space-y-3">
                            <label class="block text-sm font-semibold text-slate-700">ملف الشعار (صورة)</label>
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-accent transition duration-200 relative cursor-pointer">
                                <input type="file" name="logo_file" id="logo_file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                                <p class="text-sm font-semibold text-slate-600">اضغط لرفع ملف أو اسحبه هنا</p>
                                <p class="text-xs text-slate-400 mt-1">يدعم PNG, JPG, SVG (بحد أقصى 2 ميجابايت)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- كارت إعدادات الأداء والتحميل المسرّع -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-6">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-4">
                        <i class="fa-solid fa-gauge-high text-accent"></i>
                        إعدادات الأداء والتحميل المسرّع (لتحمل مئات الطلاب)
                    </h2>
                    
                    <div class="text-xs text-amber-700 bg-amber-50 p-4 rounded-xl border border-amber-200 flex items-start gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-base mt-0.5 flex-shrink-0"></i>
                        <div>
                            <span class="font-bold">تنبيه للمشرف:</span> هذه الإعدادات تمكن النظام من العمل بسرعة فائقة وتحمل عدد كبير من الطلاب في نفس الوقت (50 إلى 300 طالب متزامن) دون تجميد للسيرفر أو استهلاك للذاكرة.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- مدة الذاكرة المؤقتة (الكاش) -->
                        <div>
                            <label for="cache_duration" class="block text-sm font-semibold text-slate-700 mb-2">مدة الذاكرة المؤقتة (الكاش) للملفات والمجلدات</label>
                            <select name="cache_duration" id="cache_duration" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none transition duration-200 text-sm">
                                <option value="0" {{ ($settings->cache_duration ?? 5) == 0 ? 'selected' : '' }}>تعطيل الكاش (لا ينصح به تحت الضغط العالي)</option>
                                <option value="1" {{ ($settings->cache_duration ?? 5) == 1 ? 'selected' : '' }}>دقيقة واحدة (تحديث مستمر)</option>
                                <option value="5" {{ ($settings->cache_duration ?? 5) == 5 ? 'selected' : '' }}>5 دقائق (مستحسن للأداء العالي والشبكات)</option>
                                <option value="10" {{ ($settings->cache_duration ?? 5) == 10 ? 'selected' : '' }}>10 دقائق</option>
                                <option value="30" {{ ($settings->cache_duration ?? 5) == 30 ? 'selected' : '' }}>30 دقيقة</option>
                                <option value="60" {{ ($settings->cache_duration ?? 5) == 60 ? 'selected' : '' }}>ساعة كاملة</option>
                            </select>
                            <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">
                                تفعيل الكاش يخفف عمليات مسح القرص الصلب للسيرفر، حيث يقرأ البيانات من الذاكرة المؤقتة ويريح السيرفر تماماً.
                            </p>
                        </div>

                        <!-- طريقة بث الملفات -->
                        <div>
                            <label for="file_serving_mode" class="block text-sm font-semibold text-slate-700 mb-2">طريقة تشغيل وتحميل الملفات (File Serving Method)</label>
                            <select name="file_serving_mode" id="file_serving_mode" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none transition duration-200 text-sm">
                                <option value="php" {{ ($settings->file_serving_mode ?? 'php') == 'php' ? 'selected' : '' }}>البث الافتراضي عبر PHP (Standard PHP)</option>
                                <option value="x_sendfile" {{ ($settings->file_serving_mode ?? 'php') == 'x_sendfile' ? 'selected' : '' }}>تحميل مسرّع عبر Apache X-Sendfile (ينصح به مع Laragon)</option>
                                <option value="x_accel_redirect" {{ ($settings->file_serving_mode ?? 'php') == 'x_accel_redirect' ? 'selected' : '' }}>تحميل مسرّع عبر Nginx X-Accel-Redirect</option>
                            </select>
                            <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">
                                هذه التقنية تحرر ذاكرة السيرفر فوراً عند تحميل الطلاب للملفات الكبيرة (كالمحاضرات) وتقي السيرفر من الانهيار.
                            </p>
                        </div>
                    </div>

                    <!-- مسار Nginx الداخلي -->
                    <div id="nginx_path_group" class="{{ ($settings->file_serving_mode ?? 'php') == 'x_accel_redirect' ? '' : 'hidden' }} space-y-2">
                        <label for="nginx_internal_path" class="block text-sm font-semibold text-slate-700">المسار الداخلي الافتراضي لـ Nginx (Internal URI)</label>
                        <input type="text" name="nginx_internal_path" id="nginx_internal_path" value="{{ $settings->nginx_internal_path ?? '/protected-files' }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none transition duration-200 text-sm font-mono">
                        <p class="text-xs text-slate-400">
                            يجب أن يتطابق مع المسار الداخلي المهيأ في ملف إعدادات خادم Nginx.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ================= القسم السفلي (تخصيص الألوان والمعاينة متقابلان) ================= -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                
                <!-- لوحة الألوان (اليمين) -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-6">
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-4">
                            <i class="fa-solid fa-palette text-accent"></i>
                            تخصيص ألوان المكتبة
                        </h2>

                        <!-- Color Presets -->
                        <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                            <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-palette text-accent"></i> السمات اللونية الجاهزة
                            </h3>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <!-- Preset 1 -->
                                <button type="button" class="theme-preset-btn p-3 bg-white border border-slate-200 rounded-xl flex flex-col gap-2 hover:border-slate-400 text-right transition"
                                    data-primary="#1E293B" data-accent="#D97706" data-bglight="#F8FAFC">
                                    <span class="font-bold text-xs text-slate-700">الكحلي الكلاسيكي</span>
                                    <div class="flex gap-1.5 mt-1">
                                        <span class="w-4 h-4 rounded-full" style="background-color: #1E293B"></span>
                                        <span class="w-4 h-4 rounded-full" style="background-color: #D97706"></span>
                                        <span class="w-4 h-4 rounded-full border border-slate-200" style="background-color: #F8FAFC"></span>
                                    </div>
                                </button>
                                <!-- Preset 2 -->
                                <button type="button" class="theme-preset-btn p-3 bg-white border border-slate-200 rounded-xl flex flex-col gap-2 hover:border-slate-400 text-right transition"
                                    data-primary="#064E3B" data-accent="#10B981" data-bglight="#F0FDF4">
                                    <span class="font-bold text-xs text-slate-700">الغابة الخضراء</span>
                                    <div class="flex gap-1.5 mt-1">
                                        <span class="w-4 h-4 rounded-full" style="background-color: #064E3B"></span>
                                        <span class="w-4 h-4 rounded-full" style="background-color: #10B981"></span>
                                        <span class="w-4 h-4 rounded-full border border-slate-200" style="background-color: #F0FDF4"></span>
                                    </div>
                                </button>
                                <!-- Preset 3 -->
                                <button type="button" class="theme-preset-btn p-3 bg-white border border-slate-200 rounded-xl flex flex-col gap-2 hover:border-slate-400 text-right transition"
                                    data-primary="#0F172A" data-accent="#0284C7" data-bglight="#F0F9FF">
                                    <span class="font-bold text-xs text-slate-700">أزرق المحيط</span>
                                    <div class="flex gap-1.5 mt-1">
                                        <span class="w-4 h-4 rounded-full" style="background-color: #0F172A"></span>
                                        <span class="w-4 h-4 rounded-full" style="background-color: #0284C7"></span>
                                        <span class="w-4 h-4 rounded-full border border-slate-200" style="background-color: #F0F9FF"></span>
                                    </div>
                                </button>
                                <!-- Preset 4 -->
                                <button type="button" class="theme-preset-btn p-3 bg-white border border-slate-200 rounded-xl flex flex-col gap-2 hover:border-slate-400 text-right transition"
                                    data-primary="#3B0764" data-accent="#8B5CF6" data-bglight="#FAF5FF">
                                    <span class="font-bold text-xs text-slate-700">البنفسجي الملكي</span>
                                    <div class="flex gap-1.5 mt-1">
                                        <span class="w-4 h-4 rounded-full" style="background-color: #3B0764"></span>
                                        <span class="w-4 h-4 rounded-full" style="background-color: #8B5CF6"></span>
                                        <span class="w-4 h-4 rounded-full border border-slate-200" style="background-color: #FAF5FF"></span>
                                    </div>
                                </button>
                                <!-- Preset 5 -->
                                <button type="button" class="theme-preset-btn p-3 bg-white border border-slate-200 rounded-xl flex flex-col gap-2 hover:border-slate-400 text-right transition"
                                    data-primary="#450A0A" data-accent="#EF4444" data-bglight="#FEF2F2">
                                    <span class="font-bold text-xs text-slate-700">الأحمر القرمزي</span>
                                    <div class="flex gap-1.5 mt-1">
                                        <span class="w-4 h-4 rounded-full" style="background-color: #450A0A"></span>
                                        <span class="w-4 h-4 rounded-full" style="background-color: #EF4444"></span>
                                        <span class="w-4 h-4 rounded-full border border-slate-200" style="background-color: #FEF2F2"></span>
                                    </div>
                                </button>
                                <!-- Preset 6 -->
                                <button type="button" class="theme-preset-btn p-3 bg-white border border-slate-200 rounded-xl flex flex-col gap-2 hover:border-slate-400 text-right transition"
                                    data-primary="#0F172A" data-accent="#38BDF8" data-bglight="#1E293B">
                                    <span class="font-bold text-xs text-slate-700">منتصف الليل (داكن)</span>
                                    <div class="flex gap-1.5 mt-1">
                                        <span class="w-4 h-4 rounded-full" style="background-color: #0F172A"></span>
                                        <span class="w-4 h-4 rounded-full" style="background-color: #38BDF8"></span>
                                        <span class="w-4 h-4 rounded-full border border-slate-700" style="background-color: #1E293B"></span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Colors -->
                        <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                            <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-sliders text-accent"></i> تخصيص الألوان يدوياً
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="color_primary" class="block text-sm font-medium text-slate-600 mb-2">اللون الأساسي (Primary)</label>
                                    <div class="flex gap-2">
                                        <input type="color" id="picker_primary" value="{{ $settings->color_primary }}" class="w-10 h-10 rounded border border-slate-200 cursor-pointer">
                                        <input type="text" name="color_primary" id="color_primary" value="{{ $settings->color_primary }}"
                                            class="w-full px-3 py-1.5 text-sm rounded-lg border border-slate-200 uppercase outline-none focus:border-accent">
                                    </div>
                                </div>
                                <div>
                                    <label for="color_accent" class="block text-sm font-medium text-slate-600 mb-2">اللون الفرعي/التمييز (Accent)</label>
                                    <div class="flex gap-2">
                                        <input type="color" id="picker_accent" value="{{ $settings->color_accent }}" class="w-10 h-10 rounded border border-slate-200 cursor-pointer">
                                        <input type="text" name="color_accent" id="color_accent" value="{{ $settings->color_accent }}"
                                            class="w-full px-3 py-1.5 text-sm rounded-lg border border-slate-200 uppercase outline-none focus:border-accent">
                                    </div>
                                </div>
                                <div>
                                    <label for="color_bglight" class="block text-sm font-medium text-slate-600 mb-2">لون الخلفية (Background)</label>
                                    <div class="flex gap-2">
                                        <input type="color" id="picker_bglight" value="{{ $settings->color_bglight }}" class="w-10 h-10 rounded border border-slate-200 cursor-pointer">
                                        <input type="text" name="color_bglight" id="color_bglight" value="{{ $settings->color_bglight }}"
                                            class="w-full px-3 py-1.5 text-sm rounded-lg border border-slate-200 uppercase outline-none focus:border-accent">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Preview Column -->
                <div class="lg:col-span-1">
                    <div class="sticky top-6 space-y-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-eye text-slate-400"></i> معاينة مباشرة سريعة
                        </h3>
                        
                        <!-- Preview Box -->
                        <div id="preview-container" class="rounded-2xl border border-slate-200/60 shadow-lg overflow-hidden transition-all duration-300 bg-white">
                            
                            <!-- Simulated Header -->
                            <div id="preview-header" class="px-4 py-3 text-white flex justify-between items-center transition-colors duration-300">
                                <div class="font-bold text-sm flex items-center gap-2">
                                    <!-- Preview Image logo -->
                                    <img id="preview-logo-img" src="{{ $settings->logo_path ? asset($settings->logo_path) : '' }}" class="h-6 w-auto object-contain {{ ($settings->logo_type == 'image' && $settings->logo_path) ? '' : 'hidden' }}">
                                    <!-- Preview Icon logo -->
                                    <i id="preview-logo-icon" class="{{ $settings->logo_icon }} text-lg transition-colors duration-300 {{ $settings->logo_type == 'icon' ? '' : 'hidden' }}"></i>
                                    <span id="preview-title" class="truncate max-w-[150px]">{{ $settings->name }}</span>
                                </div>
                                <div class="text-[10px] opacity-75">
                                    <i class="fa-solid fa-wifi text-emerald-400"></i> متصل
                                </div>
                            </div>

                            <!-- Simulated Body -->
                            <div id="preview-body" class="p-4 space-y-4 min-h-[300px] transition-colors duration-300" style="background-color: {{ $settings->color_bglight }}">
                                
                                <!-- Breadcrumb & back button -->
                                <div class="bg-white rounded-lg p-2.5 shadow-sm border border-slate-100 flex justify-between items-center text-xs">
                                    <div class="font-medium text-slate-500 flex items-center gap-1.5">
                                        <i id="preview-breadcrumb-icon" class="fa-solid fa-folder-open"></i>
                                        <span>المكتبة الرئيسية</span>
                                    </div>
                                    <span id="preview-back-btn" class="px-2 py-1 rounded text-[10px] text-white font-semibold flex items-center gap-1 transition-all duration-300">
                                        <i class="fa-solid fa-arrow-right"></i> عودة
                                    </span>
                                </div>

                                <!-- Folder section -->
                                <div>
                                    <h4 class="text-xs font-bold text-slate-700 mb-2 flex items-center gap-1">
                                        <i class="fa-solid fa-layer-group text-[10px]"></i> الأقسام والتخصصات
                                    </h4>
                                    <div class="grid grid-cols-3 gap-2">
                                        <!-- Folder Item -->
                                        <div class="bg-white border border-slate-200/80 rounded-lg p-2 flex flex-col items-center justify-center gap-1 shadow-sm">
                                            <i id="preview-folder-icon-1" class="fa-solid fa-folder text-lg"></i>
                                            <span class="text-[9px] font-bold text-slate-600">قسم البرمجة</span>
                                        </div>
                                        <div class="bg-white border border-slate-200/80 rounded-lg p-2 flex flex-col items-center justify-center gap-1 shadow-sm">
                                            <i id="preview-folder-icon-2" class="fa-solid fa-folder text-lg"></i>
                                            <span class="text-[9px] font-bold text-slate-600">قسم التصميم</span>
                                        </div>
                                        <div class="bg-white border border-slate-200/80 rounded-lg p-2 flex flex-col items-center justify-center gap-1 shadow-sm border-dashed">
                                            <i id="preview-folder-icon-3" class="fa-solid fa-folder-plus text-lg opacity-40"></i>
                                            <span class="text-[9px] font-bold text-slate-400">إضافة قسم</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Video section -->
                                <div>
                                    <h4 class="text-xs font-bold text-slate-700 mb-2 flex items-center gap-1">
                                        <i class="fa-solid fa-film text-[10px]"></i> المحاضرات المرئية
                                    </h4>
                                    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
                                        <div class="aspect-video w-full bg-slate-900 flex items-center justify-center relative">
                                            <i class="fa-solid fa-circle-play text-white/40 text-2xl"></i>
                                            <div class="absolute bottom-1 right-1 bg-black/60 text-[8px] text-white px-1 rounded">10:45</div>
                                        </div>
                                        <div class="p-2 flex justify-between items-center text-[10px]">
                                            <span class="font-bold text-slate-700 truncate w-3/4">محاضرة مقدمة الحاسب</span>
                                            <i class="fa-solid fa-download text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- زر حفظ الإعدادات -->
            <div class="flex justify-end pt-6 border-t border-slate-200/60">
                <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-primary hover:opacity-95 text-white font-bold rounded-xl shadow-lg transition duration-200 flex items-center justify-center gap-2 text-base">
                    <i class="fa-solid fa-floppy-disk text-lg"></i>
                    حفظ التغييرات وإعادة التحميل
                </button>
            </div>

        </form>
    </main>

    <script>
        function toggleOrgLogo(val) {
            const iconGroup = document.getElementById('org-icon-input-group');
            const imageGroup = document.getElementById('org-image-input-group');
            if(val === 'icon') {
                iconGroup.classList.remove('hidden');
                imageGroup.classList.add('hidden');
            } else {
                iconGroup.classList.add('hidden');
                imageGroup.classList.remove('hidden');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('org_logo_icon').addEventListener('input', function() {
                document.getElementById('org-logo-icon-indicator').className = this.value;
            });

            // Elements
            const nameInput = document.getElementById('name');
            const logoTypeRadios = document.querySelectorAll('input[name="logo_type"]');
            const logoIconInput = document.getElementById('logo_icon');
            const logoFileInput = document.getElementById('logo_file');
            const colorPrimaryInput = document.getElementById('color_primary');
            const colorAccentInput = document.getElementById('color_accent');
            const colorBglightInput = document.getElementById('color_bglight');
            
            const pickerPrimary = document.getElementById('picker_primary');
            const pickerAccent = document.getElementById('picker_accent');
            const pickerBglight = document.getElementById('picker_bglight');
            
            const iconInputGroup = document.getElementById('icon-input-group');
            const imageInputGroup = document.getElementById('image-input-group');
            const logoIconIndicator = document.getElementById('logo-icon-indicator');

            // Preview elements
            const navTitle = document.getElementById('nav-title');
            const navLogoIcon = document.getElementById('nav-logo-icon');
            const navLogoImg = document.getElementById('nav-logo-img');
            
            const previewTitle = document.getElementById('preview-title');
            const previewLogoIcon = document.getElementById('preview-logo-icon');
            const previewLogoImg = document.getElementById('preview-logo-img');
            const previewHeader = document.getElementById('preview-header');
            const previewBody = document.getElementById('preview-body');
            const previewBackBtn = document.getElementById('preview-back-btn');
            
            const previewFolderIcon1 = document.getElementById('preview-folder-icon-1');
            const previewFolderIcon2 = document.getElementById('preview-folder-icon-2');
            const previewFolderIcon3 = document.getElementById('preview-folder-icon-3');

            // Theme preset buttons
            const themePresetBtns = document.querySelectorAll('.theme-preset-btn');

            // Function to update preview colors
            function updateColors() {
                const primaryColor = colorPrimaryInput.value;
                const accentColor = colorAccentInput.value;
                const bglightColor = colorBglightInput.value;

                // Sync pickers
                pickerPrimary.value = primaryColor;
                pickerAccent.value = accentColor;
                pickerBglight.value = bglightColor;

                // Update preview elements
                previewHeader.style.backgroundColor = primaryColor;
                previewBody.style.backgroundColor = bglightColor;
                previewBackBtn.style.backgroundColor = primaryColor;
                
                // Color primary elements
                // Accent colors
                previewLogoIcon.style.color = accentColor;
                if (navLogoIcon) navLogoIcon.style.color = accentColor;
                
                previewFolderIcon1.style.color = accentColor;
                previewFolderIcon2.style.color = accentColor;
            }

            // Sync color picker -> text input
            pickerPrimary.addEventListener('input', function() {
                colorPrimaryInput.value = pickerPrimary.value.toUpperCase();
                updateColors();
            });
            pickerAccent.addEventListener('input', function() {
                colorAccentInput.value = pickerAccent.value.toUpperCase();
                updateColors();
            });
            pickerBglight.addEventListener('input', function() {
                colorBglightInput.value = pickerBglight.value.toUpperCase();
                updateColors();
            });

            // Sync text input -> color picker
            colorPrimaryInput.addEventListener('input', function() {
                if(/^#[0-9A-F]{6}$/i.test(colorPrimaryInput.value)) {
                    updateColors();
                }
            });
            colorAccentInput.addEventListener('input', function() {
                if(/^#[0-9A-F]{6}$/i.test(colorAccentInput.value)) {
                    updateColors();
                }
            });
            colorBglightInput.addEventListener('input', function() {
                if(/^#[0-9A-F]{6}$/i.test(colorBglightInput.value)) {
                    updateColors();
                }
            });

            // Name live update
            nameInput.addEventListener('input', function() {
                navTitle.textContent = nameInput.value;
                previewTitle.textContent = nameInput.value;
            });

            // Logo Type selection
            logoTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'icon') {
                        iconInputGroup.classList.remove('hidden');
                        imageInputGroup.classList.add('hidden');
                        
                        previewLogoIcon.classList.remove('hidden');
                        previewLogoImg.classList.add('hidden');
                        
                        if(navLogoIcon) navLogoIcon.classList.remove('hidden');
                        if(navLogoImg) navLogoImg.classList.add('hidden');
                    } else {
                        iconInputGroup.classList.add('hidden');
                        imageInputGroup.classList.remove('hidden');
                        
                        previewLogoIcon.classList.add('hidden');
                        previewLogoImg.classList.remove('hidden');
                        
                        if(navLogoIcon) navLogoIcon.classList.add('hidden');
                        if(navLogoImg) navLogoImg.classList.remove('hidden');
                    }
                });
            });

            // Icon class live update
            logoIconInput.addEventListener('input', function() {
                const iconClass = logoIconInput.value || 'fa-solid fa-graduation-cap';
                
                // Update indicator in input field
                logoIconIndicator.className = iconClass;
                
                // Update preview logo
                previewLogoIcon.className = iconClass + ' text-lg transition-colors duration-300';
                if (navLogoIcon) navLogoIcon.className = iconClass + ' text-accent';
            });

            // Logo file image preview
            logoFileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewLogoImg.src = e.target.result;
                        previewLogoImg.classList.remove('hidden');
                        
                        if (navLogoImg) {
                            navLogoImg.src = e.target.result;
                            navLogoImg.classList.remove('hidden');
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Preset theme buttons click handler
            themePresetBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const primary = this.dataset.primary;
                    const accent = this.dataset.accent;
                    const bglight = this.dataset.bglight;

                    colorPrimaryInput.value = primary;
                    colorAccentInput.value = accent;
                    colorBglightInput.value = bglight;

                    updateColors();
                });
            });

            // تم نقل إدارة الأقسام لصفحة مستقلة

            // File Serving Mode Nginx settings toggle
            const fileServingModeSelect = document.getElementById('file_serving_mode');
            const nginxPathGroup = document.getElementById('nginx_path_group');
            if (fileServingModeSelect && nginxPathGroup) {
                fileServingModeSelect.addEventListener('change', function() {
                    if (this.value === 'x_accel_redirect') {
                        nginxPathGroup.classList.remove('hidden');
                    } else {
                        nginxPathGroup.classList.add('hidden');
                    }
                });
            }

            // Initial trigger
            updateColors();
        });
    </script>
    @include('components.footer')
</body>
</html>
