<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->name }}</title>
    
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
        /* تحسين شكل مشغل الفيديو */
        video {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            background-color: #000;
        }
    </style>
</head>
<body class="bg-bglight text-slate-800 font-sans antialiased">

    <nav class="bg-primary text-white shadow-lg transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ url('/') }}" class="text-2xl font-bold flex items-center gap-2 hover:opacity-90 transition" title="الصفحة الترحيبية">
                @if($settings->logo_type === 'image' && $settings->logo_path)
                    <img src="{{ asset($settings->logo_path) }}" alt="Logo" class="h-8 w-auto object-contain">
                @else
                    <i class="{{ $settings->logo_icon }} text-accent"></i>
                @endif
                <span>{{ $settings->name }}</span>
            </a>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-300 flex items-center gap-1.5"><i class="fa-solid fa-wifi text-green-400"></i> <span class="hidden sm:inline">متصل بالشبكة المحلية</span></span>
                <a href="{{ route('settings.index') }}" class="hover:text-accent text-slate-200 transition duration-300" title="الإعدادات">
                    <i class="fa-solid fa-gear text-lg"></i>
                </a>
            </div>
        </div>
    </nav> 

    <main class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- التبويبات الرئيسية لتنقل السيرفر -->
        <div class="flex flex-col sm:flex-row border-b border-slate-200 mb-8 bg-white p-2 rounded-xl shadow-sm gap-2">
            <a href="{{ url('/library?type=videos') }}" class="flex-1 py-3 px-4 rounded-lg font-bold text-center transition flex items-center justify-center gap-2 {{ $type === 'videos' ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-film"></i> المحاضرات المرئية
            </a>
            <a href="{{ url('/library?type=books') }}" class="flex-1 py-3 px-4 rounded-lg font-bold text-center transition flex items-center justify-center gap-2 {{ $type === 'books' ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-book-open"></i> الكتب والمراجع
            </a>
            <a href="{{ url('/library?type=programs') }}" class="flex-1 py-3 px-4 rounded-lg font-bold text-center transition flex items-center justify-center gap-2 {{ $type === 'programs' ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-laptop-code"></i> البرامج والأدوات
            </a>
        </div>

        <!-- مسار التصفح الحالي -->
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center sm:justify-between mb-8 bg-white p-4 rounded-lg shadow-sm border border-slate-100">
            <div class="text-lg font-semibold text-slate-600 flex items-center gap-2 max-w-full overflow-hidden">
                <i class="fa-solid fa-folder-open text-accent flex-shrink-0"></i>
                <span class="truncate">
                    @php
                        $typeLabel = match($type) {
                            'books' => 'الكتب والمراجع',
                            'programs' => 'البرامج والأدوات',
                            default => 'المحاضرات المرئية',
                        };
                    @endphp
                    {{ $typeLabel }}
                    @if($currentFolder != '')
                        / {{ str_replace('/', ' / ', $currentFolder) }}
                    @endif
                </span>
            </div>
            
            <div class="flex items-center gap-2 flex-shrink-0 w-full sm:w-auto">
                @if($currentFolder != '')
                    <a href="{{ url('/library?type=' . $type) }}" class="w-full sm:w-auto text-center justify-center bg-primary hover:opacity-90 text-white px-4 py-2 rounded-md transition duration-300 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-arrow-right"></i> عودة للمجلد الرئيسي
                    </a>
                @else
                    <a href="{{ url('/') }}" class="w-full sm:w-auto text-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-md transition duration-300 text-sm flex items-center gap-2 font-bold">
                        <i class="fa-solid fa-house"></i> الصفحة الترحيبية
                    </a>
                @endif
            </div>
        </div>

        <!-- عرض المجلدات الفرعية إن وجدت -->
        @if(count($folders) > 0)
        <div class="mb-10">
            <h2 class="text-xl font-bold mb-4 text-slate-700"><i class="fa-solid fa-layer-group ml-2 text-primary"></i>الأقسام والتخصصات الفرعية</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($folders as $folder)
                    @php 
                        $nextFolder = $currentFolder ? $currentFolder . '/' . $folder : $folder;
                    @endphp
                    <a href="{{ url('/library?type=' . $type . '&folder=' . $nextFolder) }}" class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center gap-3 hover:shadow-md hover:border-accent transition duration-300 group cursor-pointer">
                        <i class="fa-solid fa-folder text-4xl text-accent group-hover:scale-110 transition duration-300"></i>
                        <span class="font-semibold text-slate-700 text-center text-sm">{{ $folder }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- عرض الملفات حسب نوع القسم المفتوح -->
        @if(count($files) > 0)
        <div>
            <h2 class="text-xl font-bold mb-4 text-slate-700">
                @if($type === 'videos')
                    <i class="fa-solid fa-film ml-2 text-primary"></i>المحاضرات المرئية المتوفرة
                @elseif($type === 'books')
                    <i class="fa-solid fa-book-open ml-2 text-primary"></i>الكتب والملفات الدراسية
                @else
                    <i class="fa-solid fa-laptop-code ml-2 text-primary"></i>البرامج والتطبيقات
                @endif
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($files as $file)
                    @php
                        $fileQueryPath = ($currentFolder ? $currentFolder . '/' : '') . $file['name'];
                    @endphp
                    
                    @if($type === 'videos')
                        <!-- كارت الفيديو -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition duration-300">
                            <div class="aspect-video w-full bg-black relative">
                                <video controls preload="none" class="w-full h-full object-cover">
                                    <source src="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" type="video/mp4">
                                    متصفحك لا يدعم التشغيل.
                                </video>
                            </div>
                            <div class="p-4 flex justify-between items-center">
                                <h4 class="font-bold text-slate-800 text-sm truncate w-3/4" title="{{ $file['name'] }}">
                                    <i class="fa-solid fa-circle-play text-accent ml-1"></i> {{ $file['name_without_ext'] }}
                                </h4>
                                <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" download class="text-slate-400 hover:text-primary transition" title="تحميل">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                        </div>
                    @elseif($type === 'books')
                        <!-- كارت الكتب والمراجع -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition duration-300 p-5 flex flex-col justify-between h-48">
                            <div class="flex items-start gap-4">
                                @php
                                    $bgClass = 'bg-slate-50 text-slate-500';
                                    $iconClass = 'fa-solid fa-file-lines';
                                    if ($file['extension'] === 'pdf') {
                                        $bgClass = 'bg-rose-50 text-rose-500';
                                        $iconClass = 'fa-solid fa-file-pdf';
                                    } elseif (in_array($file['extension'], ['docx', 'doc'])) {
                                        $bgClass = 'bg-blue-50 text-blue-500';
                                        $iconClass = 'fa-solid fa-file-word';
                                    } elseif (in_array($file['extension'], ['xlsx', 'xls'])) {
                                        $bgClass = 'bg-emerald-50 text-emerald-600';
                                        $iconClass = 'fa-solid fa-file-excel';
                                    } elseif (in_array($file['extension'], ['pptx', 'ppt'])) {
                                        $bgClass = 'bg-orange-50 text-orange-600';
                                        $iconClass = 'fa-solid fa-file-powerpoint';
                                    } elseif ($file['extension'] === 'epub') {
                                        $bgClass = 'bg-indigo-50 text-indigo-600';
                                        $iconClass = 'fa-solid fa-book';
                                    }
                                @endphp
                                <div class="p-3 {{ $bgClass }} rounded-lg text-3xl flex-shrink-0">
                                    <i class="{{ $iconClass }}"></i>
                                </div>
                                <div class="truncate w-full">
                                    <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $file['name'] }}">
                                        {{ $file['name_without_ext'] }}
                                    </h4>
                                    <p class="text-xs text-slate-400 mt-1">صيغة: {{ strtoupper($file['extension']) }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">الحجم: {{ $file['size'] }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-4 pt-3 border-t border-slate-50">
                                <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" target="_blank" 
                                   class="flex-1 text-center py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-eye"></i> قراءة
                                </a>
                                <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" download
                                   class="flex-1 text-center py-2 bg-primary hover:opacity-90 text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-download"></i> تحميل
                                </a>
                            </div>
                        </div>
                    @elseif($type === 'programs')
                        <!-- كارت البرامج والأدوات -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition duration-300 p-5 flex flex-col justify-between h-48">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg text-3xl flex-shrink-0">
                                    @if(in_array($file['extension'], ['zip', 'rar', '7z']))
                                        <i class="fa-solid fa-file-zipper text-amber-500"></i>
                                    @else
                                        <i class="fa-solid fa-laptop-code text-indigo-500"></i>
                                    @endif
                                </div>
                                <div class="truncate w-full">
                                    <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $file['name'] }}">
                                        {{ $file['name_without_ext'] }}
                                    </h4>
                                    <p class="text-xs text-slate-400 mt-1">صيغة: {{ strtoupper($file['extension']) }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">الحجم: {{ $file['size'] }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-4 pt-3 border-t border-slate-50">
                                <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" download
                                   class="w-full text-center py-2 bg-primary hover:opacity-90 text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-download"></i> تنزيل البرنامج
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- عرض التنبيه في حال خلو المجلد والملفات -->
        @if(empty($folders) && empty($files))
            <div class="flex flex-col items-center justify-center py-20 text-slate-400 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <i class="fa-solid fa-box-open text-6xl mb-4"></i>
                <p class="text-lg">هذا القسم لا يحتوي على ملفات أو مجلدات حالياً.</p>
            </div>
        @endif

    </main>
</body>
</html>