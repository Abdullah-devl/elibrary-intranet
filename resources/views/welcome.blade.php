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
            <div class="flex items-center gap-3 sm:gap-4">
                @if(session('admin_authenticated'))
                    <a href="{{ request()->fullUrlWithQuery(['refresh' => 1]) }}" 
                       class="bg-accent hover:opacity-90 text-white text-xs px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 transition font-bold shadow-sm" 
                       title="تحديث ملفات المجلد الحالي (تفريغ الكاش)">
                        <i class="fa-solid fa-rotate text-sm"></i>
                        <span class="hidden md:inline">تحديث قائمة الملفات</span>
                    </a>
                @endif
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
            @foreach($settings->categories ?? [] as $cat)
                @php
                    $cat = (array) $cat;
                @endphp
                <a href="{{ url('/library?type=' . $cat['id']) }}" class="flex-1 py-3 px-4 rounded-lg font-bold text-center transition flex items-center justify-center gap-2 {{ $type === $cat['id'] ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    @if(!empty($cat['image_path']))
                        <img src="{{ asset($cat['image_path']) }}" class="w-6 h-6 object-cover rounded-md flex-shrink-0">
                    @else
                        <i class="{{ $cat['icon'] ?? 'fa-solid fa-folder' }}"></i>
                    @endif
                    {{ $cat['name'] }}
                </a>
            @endforeach
        </div>

        <!-- مسار التصفح الحالي -->
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center sm:justify-between mb-8 bg-white p-4 rounded-lg shadow-sm border border-slate-100">
            <div class="text-lg font-semibold text-slate-600 flex items-center gap-2 max-w-full overflow-hidden">
                <i class="fa-solid fa-folder-open text-accent flex-shrink-0"></i>
                <span class="truncate">
                    @php
                        $currentCategory = (array) $currentCategory;
                        $typeLabel = $currentCategory['name'] ?? '';
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
            @php
                $currentCategory = (array) $currentCategory;
                $layout = $currentCategory['layout'] ?? 'video';
            @endphp
            <h2 class="text-xl font-bold mb-4 text-slate-700">
                <i class="{{ $currentCategory['icon'] ?? 'fa-solid fa-folder-open' }} ml-2 text-primary"></i>{{ $currentCategory['name'] }} المتوفرة
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($files as $file)
                    @php
                        $fileQueryPath = ($currentFolder ? $currentFolder . '/' : '') . $file['name'];
                        $isImage = in_array($file['extension'], ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico']);
                    @endphp
                    
                    @if($layout === 'video')
                        <!-- كارت الفيديو أو الصورة -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition duration-300">
                            @if($isImage)
                                <div class="aspect-video w-full bg-slate-50 relative overflow-hidden flex items-center justify-center border-b border-slate-100">
                                    <img src="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" class="w-full h-full object-cover animate-fade-in" alt="{{ $file['name'] }}" loading="lazy">
                                </div>
                                <div class="p-4 flex justify-between items-center">
                                    <h4 class="font-bold text-slate-800 text-sm truncate w-2/3" title="{{ $file['name'] }}">
                                        <i class="fa-solid fa-image text-accent ml-1"></i> {{ $file['name_without_ext'] }}
                                    </h4>
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <button onclick="openUsbModal('{{ $type }}', '{{ $fileQueryPath }}', '{{ $file['name'] }}')" class="text-slate-400 hover:text-accent transition text-base" title="إرسال لفلاش السيرفر (USB)">
                                            <i class="fa-solid fa-usb"></i>
                                        </button>
                                        <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" download class="text-slate-400 hover:text-primary transition text-base" title="تحميل">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="aspect-video w-full bg-black relative">
                                    <video controls preload="none" class="w-full h-full object-cover">
                                        <source src="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" type="video/mp4">
                                        متصفحك لا يدعم التشغيل.
                                    </video>
                                </div>
                                <div class="p-4 flex justify-between items-center">
                                    <h4 class="font-bold text-slate-800 text-sm truncate w-2/3" title="{{ $file['name'] }}">
                                        <i class="fa-solid fa-circle-play text-accent ml-1"></i> {{ $file['name_without_ext'] }}
                                    </h4>
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <button onclick="openUsbModal('{{ $type }}', '{{ $fileQueryPath }}', '{{ $file['name'] }}')" class="text-slate-400 hover:text-accent transition text-base" title="إرسال لفلاش السيرفر (USB)">
                                            <i class="fa-solid fa-usb"></i>
                                        </button>
                                        <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" download class="text-slate-400 hover:text-primary transition text-base" title="تحميل">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @elseif($layout === 'document')
                        <!-- كارت الكتب والمراجع والمستندات -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition duration-300 p-5 flex flex-col justify-between h-48">
                            <div class="flex items-start gap-4">
                                @if($isImage)
                                    <div class="w-14 h-14 rounded-lg overflow-hidden bg-slate-100 border border-slate-200/80 shadow-inner flex-shrink-0 flex items-center justify-center font-semibold">
                                        <img src="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" class="w-full h-full object-cover" alt="{{ $file['name'] }}" loading="lazy">
                                    </div>
                                @else
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
                                    <div class="p-3 {{ $bgClass }} rounded-lg text-3xl flex-shrink-0 font-semibold">
                                        <i class="{{ $iconClass }}"></i>
                                    </div>
                                @endif
                                <div class="truncate w-full font-semibold">
                                    <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $file['name'] }}">
                                        {{ $file['name_without_ext'] }}
                                    </h4>
                                    <p class="text-xs text-slate-400 mt-1">صيغة: {{ strtoupper($file['extension']) }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">الحجم: {{ $file['size'] }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-4 pt-3 border-t border-slate-50">
                                <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" target="_blank" 
                                   class="flex-1 text-center py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-lg text-[10px] sm:text-xs font-bold transition flex items-center justify-center gap-1 border border-slate-200/50">
                                    <i class="fa-solid fa-eye"></i> {{ $isImage ? 'عرض' : 'قراءة' }}
                                </a>
                                <button onclick="openUsbModal('{{ $type }}', '{{ $fileQueryPath }}', '{{ $file['name'] }}')"
                                   class="flex-1 text-center py-2 bg-accent/10 hover:bg-accent/20 text-accent rounded-lg text-[10px] sm:text-xs font-bold transition flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-usb"></i> إرسال لـ USB
                                </button>
                                <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" download
                                   class="flex-1 text-center py-2 bg-primary hover:opacity-90 text-white rounded-lg text-[10px] sm:text-xs font-bold transition flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-download"></i> تحميل
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- كارت البرامج والأدوات والتنزيلات المباشرة -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition duration-300 p-5 flex flex-col justify-between h-48">
                            <div class="flex items-start gap-4">
                                @if($isImage)
                                    <div class="w-14 h-14 rounded-lg overflow-hidden bg-slate-100 border border-slate-200/80 shadow-inner flex-shrink-0 flex items-center justify-center font-semibold">
                                        <img src="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" class="w-full h-full object-cover" alt="{{ $file['name'] }}" loading="lazy">
                                    </div>
                                @else
                                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg text-3xl flex-shrink-0 font-semibold">
                                        @if(in_array($file['extension'], ['zip', 'rar', '7z']))
                                            <i class="fa-solid fa-file-zipper text-amber-500"></i>
                                        @else
                                            <i class="fa-solid fa-laptop-code text-indigo-500"></i>
                                        @endif
                                    </div>
                                @endif
                                <div class="truncate w-full font-semibold">
                                    <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $file['name'] }}">
                                        {{ $file['name_without_ext'] }}
                                    </h4>
                                    <p class="text-xs text-slate-400 mt-1">صيغة: {{ strtoupper($file['extension']) }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">الحجم: {{ $file['size'] }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-4 pt-3 border-t border-slate-50">
                                @if($isImage)
                                    <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" target="_blank" 
                                       class="flex-1 text-center py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-lg text-[10px] sm:text-xs font-bold transition flex items-center justify-center gap-1 border border-slate-200/50">
                                        <i class="fa-solid fa-eye"></i> عرض
                                    </a>
                                    <button onclick="openUsbModal('{{ $type }}', '{{ $fileQueryPath }}', '{{ $file['name'] }}')"
                                       class="flex-1 text-center py-2 bg-accent/10 hover:bg-accent/20 text-accent rounded-lg text-[10px] sm:text-xs font-bold transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-usb"></i> إرسال لـ USB
                                    </button>
                                    <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" download
                                       class="flex-1 text-center py-2 bg-primary hover:opacity-90 text-white rounded-lg text-[10px] sm:text-xs font-bold transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-download"></i> تنزيل
                                    </a>
                                @else
                                    <button onclick="openUsbModal('{{ $type }}', '{{ $fileQueryPath }}', '{{ $file['name'] }}')"
                                       class="flex-1 text-center py-2 bg-accent/10 hover:bg-accent/20 text-accent rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-usb"></i> نسخ لـ USB
                                    </button>
                                    <a href="{{ route('file.serve', ['type' => $type, 'file' => $fileQueryPath]) }}" download
                                       class="flex-1 text-center py-2 bg-primary hover:opacity-90 text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-download"></i> تنزيل الملف
                                    </a>
                                @endif
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

    <!-- نافذة منبثقة لنسخ الملفات لـ USB (USB Copy Modal) -->
    <div id="usb-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <!-- خلفية ضبابية -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"></div>

        <!-- المودال نفسه -->
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-right shadow-2xl transition-all duration-300 w-full max-w-md p-6 my-8 border border-white/20">
                
                <!-- هيدر المودال -->
                <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-4">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-usb text-accent animate-pulse"></i>
                        <span>نسخ مباشر إلى فلاش السيرفر (USB)</span>
                    </h3>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button type="button" onclick="minimizeUsbModal()" class="text-slate-400 hover:text-slate-600 transition p-1.5 hover:bg-slate-50 rounded-lg animate-fade-in" title="تصغير النافذة لمتابعة التصفح">
                            <i class="fa-solid fa-minus text-base"></i>
                        </button>
                        <button type="button" onclick="closeUsbModal()" class="text-slate-400 hover:text-slate-600 transition p-1.5 hover:bg-slate-50 rounded-lg" title="إغلاق">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- محتوى المودال -->
                <div class="space-y-4">
                    <!-- تفاصيل الملف الحالي -->
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 text-xs text-slate-600 space-y-1">
                        <div><span class="font-bold text-slate-700">اسم الملف:</span> <span id="usb-modal-file-name" class="font-mono text-slate-800 break-all font-semibold"></span></div>
                    </div>

                    <!-- حالة البحث / اللودر -->
                    <div id="usb-drives-loading" class="flex flex-col items-center justify-center py-8 space-y-3">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-primary"></i>
                        <span class="text-sm text-slate-500 font-bold">جاري الكشف عن الأقراص والفلاشات المتصلة بالسيرفر...</span>
                    </div>

                    <!-- قائمة الأقراص المتوفرة -->
                    <div id="usb-drives-list" class="space-y-2 max-h-60 overflow-y-auto hidden">
                        <!-- سيتم تعبئتها بواسطة Javascript -->
                    </div>

                    <!-- رسالة عدم وجود أقراص -->
                    <div id="usb-no-drives" class="text-center py-6 hidden space-y-3">
                        <i class="fa-solid fa-triangle-exclamation text-4xl text-amber-500"></i>
                        <p class="text-sm text-slate-600 font-bold">لم يتم العثور على أي أقراص أو فلاشات USB موصولة بالسيرفر حالياً.</p>
                        <button type="button" onclick="refreshDrives()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-extrabold rounded-xl transition flex items-center gap-1.5 mx-auto">
                            <i class="fa-solid fa-rotate"></i> تحديث القائمة
                        </button>
                    </div>

                    <!-- حالة جاري النسخ -->
                    <div id="usb-copying-state" class="flex flex-col items-center justify-center py-8 space-y-3 hidden">
                        <i class="fa-solid fa-spinner fa-spin text-4xl text-accent"></i>
                        <span class="text-sm text-slate-700 font-bold">جاري نسخ الملف بسرعة فائقة إلى الفلاش ميموري...</span>
                        <span class="text-[10px] text-slate-400">يرجى عدم فصل الفلاش ميموري أو إغلاق الصفحة</span>
                    </div>

                    <!-- رسالة النجاح أو الفشل -->
                    <div id="usb-status-message" class="hidden p-4 rounded-xl border text-sm space-y-2">
                        <!-- سيتم تعبئتها بواسطة Javascript -->
                    </div>
                </div>

                <!-- ذيل المودال -->
                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 mt-5">
                    <button type="button" id="btn-usb-close" onclick="closeUsbModal()" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-xs">
                        إغلاق
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- الويجت المصغر لمتابعة النسخ أسفل الشاشة (Floating Minimized Widget) -->
    <div id="usb-minimized-widget" class="fixed bottom-5 left-5 bg-white border border-slate-200/80 shadow-2xl rounded-2xl p-4 flex items-center gap-3.5 max-w-sm transition-all duration-300 transform translate-y-20 opacity-0 pointer-events-none z-50">
        <!-- الأيقونة التفاعلية -->
        <div id="usb-minimized-icon-container" class="w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center flex-shrink-0 animate-pulse">
            <i class="fa-solid fa-usb text-sm"></i>
        </div>
        <!-- نصوص الحالة -->
        <div class="truncate flex-grow min-w-0">
            <h5 id="usb-minimized-title" class="text-xs font-extrabold text-slate-800">جاري الكشف عن الأقراص...</h5>
            <p id="usb-minimized-file-name" class="text-[10px] text-slate-400 truncate max-w-[160px] font-semibold"></p>
        </div>
        <!-- أزرار التحكم -->
        <div class="flex items-center gap-1.5 flex-shrink-0">
            <button type="button" onclick="maximizeUsbModal()" class="text-slate-400 hover:text-primary transition p-1.5 hover:bg-slate-50 rounded-lg text-sm" title="تكبير وتفصيل">
                <i class="fa-solid fa-expand"></i>
            </button>
            <button type="button" id="usb-minimized-close-btn" onclick="closeUsbMinimizedWidget()" class="text-slate-400 hover:text-rose-600 transition p-1.5 hover:bg-slate-50 rounded-lg text-sm hidden" title="إغلاق">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <script>
        let currentUsbType = '';
        let currentUsbFilePath = '';

        function openUsbModal(type, fileQueryPath, fileName) {
            currentUsbType = type;
            currentUsbFilePath = fileQueryPath;
            
            // تعيين اسم الملف في هيدر المودال والويجت المصغر
            document.getElementById('usb-modal-file-name').textContent = fileName;
            document.getElementById('usb-minimized-file-name').textContent = fileName;
            
            // إعادة ضبط حالة الويجت المصغر
            updateMinimizedWidgetStatus('copying', 'جاري كشف فلاشات السيرفر...');
            
            // إظهار المودال وتعيين الحالة الافتراضية
            const modal = document.getElementById('usb-modal');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            
            // جلب الأقراص
            refreshDrives();
        }

        function closeUsbModal() {
            // إخفاء المودال وإعادة تفعيل التمرير للجسم
            const modal = document.getElementById('usb-modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            closeUsbMinimizedWidget();
        }

        function minimizeUsbModal() {
            // إخفاء المودال وتفعيل التمرير لمتابعة التصفح
            const modal = document.getElementById('usb-modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            
            // إظهار الويجت المصغر
            const widget = document.getElementById('usb-minimized-widget');
            widget.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            widget.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');
        }

        function maximizeUsbModal() {
            // إخفاء الويجت المصغر
            const widget = document.getElementById('usb-minimized-widget');
            widget.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
            widget.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            
            // إظهار المودال الرئيسي وإيقاف السكرول
            const modal = document.getElementById('usb-modal');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeUsbMinimizedWidget() {
            const widget = document.getElementById('usb-minimized-widget');
            widget.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
            widget.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
        }

        function updateMinimizedWidgetStatus(state, message) {
            const titleEl = document.getElementById('usb-minimized-title');
            const iconEl = document.getElementById('usb-minimized-icon-container');
            const closeBtn = document.getElementById('usb-minimized-close-btn');

            if (titleEl) titleEl.textContent = message;
            
            if (iconEl && closeBtn) {
                if (state === 'copying') {
                    iconEl.className = 'w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center flex-shrink-0 animate-pulse';
                    iconEl.innerHTML = '<i class="fa-solid fa-usb text-sm"></i>';
                    closeBtn.classList.add('hidden');
                } else if (state === 'success') {
                    iconEl.className = 'w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0';
                    iconEl.innerHTML = '<i class="fa-solid fa-circle-check text-sm"></i>';
                    closeBtn.classList.remove('hidden');
                    // إخفاء الويجت تلقائياً بعد 6 ثوانٍ
                    setTimeout(() => {
                        closeUsbMinimizedWidget();
                    }, 6000);
                } else if (state === 'error') {
                    iconEl.className = 'w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0';
                    iconEl.innerHTML = '<i class="fa-solid fa-circle-xmark text-sm"></i>';
                    closeBtn.classList.remove('hidden');
                }
            }
        }

        function refreshDrives() {
            // إظهار اللودر وإخفاء العناصر الأخرى
            document.getElementById('usb-drives-loading').classList.remove('hidden');
            document.getElementById('usb-drives-list').classList.add('hidden');
            document.getElementById('usb-no-drives').classList.add('hidden');
            document.getElementById('usb-copying-state').classList.add('hidden');
            document.getElementById('usb-status-message').classList.add('hidden');
            document.getElementById('btn-usb-close').removeAttribute('disabled');

            // طلب أجاكس للكشف عن الأقراص
            fetch('{{ route("library.detect_drives") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('usb-drives-loading').classList.add('hidden');
                    
                    if (data.success && data.drives && data.drives.length > 0) {
                        const listContainer = document.getElementById('usb-drives-list');
                        listContainer.innerHTML = '';
                        
                        data.drives.forEach(drive => {
                            const driveCard = document.createElement('button');
                            driveCard.type = 'button';
                            driveCard.className = 'w-full flex items-center justify-between p-3.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-primary rounded-xl transition duration-300 text-right group mb-2 last:mb-0';
                            driveCard.onclick = () => copyFileToDrive(drive.letter);
                            
                            driveCard.innerHTML = `
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-lg group-hover:scale-105 transition duration-300">
                                        <i class="fa-solid fa-hard-drive"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm">القرص ${drive.letter}: (${drive.volume_name})</h4>
                                        <p class="text-[10px] text-slate-400 mt-0.5">المساحة المتوفرة: ${drive.free} من إجمالي ${drive.total}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1.5">
                                    <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-bold">جاهز للنسخ</span>
                                    <div class="w-16 bg-slate-100 rounded-full h-1 overflow-hidden">
                                        <div class="bg-primary h-full" style="width: ${drive.percent_used}%"></div>
                                    </div>
                                </div>
                            `;
                            listContainer.appendChild(driveCard);
                        });
                        listContainer.classList.remove('hidden');
                    } else {
                        document.getElementById('usb-no-drives').classList.remove('hidden');
                        updateMinimizedWidgetStatus('error', 'لا توجد فلاشات موصولة');
                    }
                })
                .catch(error => {
                    document.getElementById('usb-drives-loading').classList.add('hidden');
                    const statusMsg = document.getElementById('usb-status-message');
                    statusMsg.className = 'p-4 rounded-xl border border-rose-100 bg-rose-50 text-rose-800 text-xs space-y-1 block';
                    statusMsg.innerHTML = `
                        <div class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-triangle-exclamation"></i> فشل الكشف عن الأقراص</div>
                        <p class="text-[10px] text-rose-600 mt-1">حدث خطأ أثناء التواصل مع السيرفر: ${error.message}</p>
                    `;
                    updateMinimizedWidgetStatus('error', 'فشل كشف الأقراص');
                });
        }

        function copyFileToDrive(driveLetter) {
            // إخفاء القائمة وإظهار حالة النسخ
            document.getElementById('usb-drives-list').classList.add('hidden');
            document.getElementById('usb-copying-state').classList.remove('hidden');
            document.getElementById('btn-usb-close').setAttribute('disabled', 'true');
            
            // تحديث حالة الويجت المصغر
            updateMinimizedWidgetStatus('copying', `جاري النسخ للقرص ${driveLetter}...`);

            // إرسال طلب النسخ بالـ POST
            fetch('{{ route("library.copy_to_drive") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    type: '{{ $type }}',
                    file: currentUsbFilePath,
                    drive: driveLetter
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('usb-copying-state').classList.add('hidden');
                document.getElementById('btn-usb-close').removeAttribute('disabled');
                
                const statusMsg = document.getElementById('usb-status-message');
                if (data.success) {
                    statusMsg.className = 'p-4 rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-800 text-xs space-y-1 block';
                    statusMsg.innerHTML = `
                        <div class="font-bold flex items-center gap-1.5 text-sm"><i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i> تمت عملية النسخ بنجاح!</div>
                        <p class="text-[10px] text-emerald-600 mt-1">${data.message}</p>
                    `;
                    updateMinimizedWidgetStatus('success', 'تم النسخ بنجاح!');
                } else {
                    statusMsg.className = 'p-4 rounded-xl border border-rose-100 bg-rose-50 text-rose-800 text-xs space-y-1 block';
                    statusMsg.innerHTML = `
                        <div class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-xmark text-rose-600 font-bold"></i> فشل نسخ الملف للقرص</div>
                        <p class="text-[10px] text-rose-600 mt-1">${data.message}</p>
                    `;
                    updateMinimizedWidgetStatus('error', 'فشل نسخ الملف!');
                }
            })
            .catch(error => {
                document.getElementById('usb-copying-state').classList.add('hidden');
                document.getElementById('btn-usb-close').removeAttribute('disabled');
                
                const statusMsg = document.getElementById('usb-status-message');
                statusMsg.className = 'p-4 rounded-xl border border-rose-100 bg-rose-50 text-rose-800 text-xs space-y-1 block';
                statusMsg.innerHTML = `
                    <div class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-triangle-exclamation"></i> حدث خطأ أثناء النسخ</div>
                    <p class="text-[10px] text-rose-600 mt-1">فشل الاتصال بالسيرفر لإتمام عملية النسخ: ${error.message}</p>
                `;
            });
        }
    </script>
    @include('components.footer')
</body>
</html>