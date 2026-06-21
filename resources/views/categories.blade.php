<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة أقسام المكتبة الإلكترونية</title>
    
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
            <div class="text-xl sm:text-2xl font-bold flex items-center gap-3 truncate max-w-[60%]">
                @if($settings->logo_type === 'image' && $settings->logo_path)
                    <img src="{{ asset($settings->logo_path) }}" alt="Logo" class="h-8 sm:h-9 w-auto object-contain flex-shrink-0">
                @else
                    <i class="{{ $settings->logo_icon }} text-accent flex-shrink-0"></i>
                @endif
                <span class="truncate">{{ $settings->name }}</span>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('settings.index') }}" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg transition duration-300 text-sm flex items-center gap-2" title="العودة للإعدادات العامة">
                    <i class="fa-solid fa-arrow-right"></i> <span>الإعدادات العامة</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 mt-8">
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-r-4 border-emerald-500 rounded-lg shadow-sm text-emerald-800 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border-r-4 border-rose-500 rounded-lg shadow-sm text-rose-800 flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border-r-4 border-rose-500 rounded-lg shadow-sm text-rose-800 space-y-2">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-xmark text-xl"></i>
                    <span class="font-semibold">يرجى إصلاح الأخطاء التالية:</span>
                </div>
                <ul class="list-disc pr-6 text-xs space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5">
                    <i class="fa-solid fa-folder-tree text-accent"></i>
                    إدارة أقسام المكتبة الرقمية
                </h1>
                <p class="text-xs text-slate-500 mt-1">يمكنك إضافة أقسام جديدة، وتحديد المسار الفعلي ومجلد الحفظ مع الصيغ النشطة لكل قسم على حدة.</p>
            </div>
            <button type="button" id="btn-add-category" class="w-full sm:w-auto px-5 py-3 bg-primary hover:opacity-95 text-white font-bold rounded-xl shadow-lg transition duration-300 flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus text-accent"></i>
                إضافة قسم جديد للمكتبة
            </button>
        </div>

        <!-- Categories List Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($categories as $cat)
                @php
                    $cat = (array) $cat;
                    $exists = $pathsStatus[$cat['id']] ?? false;
                    $extCount = is_array($cat['extensions']) ? count($cat['extensions']) : 0;
                    $layoutLabel = match($cat['layout'] ?? 'video') {
                        'video' => 'مشغل فيديو تلقائي',
                        'document' => 'مستندات وتصفح مباشر',
                        default => 'تنزيل مباشر فقط',
                    };
                    $layoutIcon = match($cat['layout'] ?? 'video') {
                        'video' => 'fa-solid fa-circle-play',
                        'document' => 'fa-solid fa-file-pdf',
                        default => 'fa-solid fa-cloud-arrow-down',
                    };
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col justify-between hover:shadow-md transition duration-300">
                    <div class="space-y-4">
                        <!-- Card Header -->
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-primary/10 text-primary flex items-center justify-center text-xl shadow-inner flex-shrink-0">
                                    @if(!empty($cat['image_path']))
                                        <img src="{{ asset($cat['image_path']) }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="{{ $cat['icon'] ?? 'fa-solid fa-folder' }}"></i>
                                    @endif
                                </div>
                                <div class="truncate max-w-[150px]">
                                    <h3 class="font-extrabold text-slate-800 text-sm truncate" title="{{ $cat['name'] }}">{{ $cat['name'] }}</h3>
                                    <span class="text-[10px] text-slate-400 font-mono font-bold">{{ $cat['id'] }}</span>
                                </div>
                            </div>
                            <!-- Status Indicator -->
                            <div class="flex items-center gap-1">
                                @if($exists)
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-[10px] text-emerald-600 font-bold">نشط</span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                    <span class="text-[10px] text-rose-500 font-bold">المسار غير متوفر</span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Info -->
                        <div class="space-y-2.5 text-xs border-t border-b border-slate-100 py-4 font-semibold text-slate-600">
                            <div class="flex justify-between items-center">
                                <span>طريقة العرض والتخطيط:</span>
                                <span class="text-slate-800 flex items-center gap-1.5"><i class="{{ $layoutIcon }} text-accent"></i> {{ $layoutLabel }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>الصيغ المسموح بعرضها:</span>
                                <span class="text-primary bg-primary/5 px-2 py-0.5 rounded font-bold font-mono text-[10px]">{{ $extCount }} صيغة نشطة</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-slate-400">مسار الملفات:</span>
                                <code class="bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100 font-mono text-[10px] text-slate-800 select-all truncate block" title="{{ $cat['path'] }}">{{ $cat['path'] }}</code>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 mt-5">
                        <button type="button" class="flex-1 text-center py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 btn-edit-category"
                                data-category="{{ json_encode($cat) }}">
                            <i class="fa-solid fa-pen-to-square text-slate-500"></i> تعديل بيانات القسم
                        </button>
                        <form action="{{ route('settings.categories.delete') }}" method="POST" class="m-0" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا القسم؟ (لن يتم حذف ملفاتك الأصلية من السيرفر)')">
                            @csrf
                            <input type="hidden" name="id" value="{{ $cat['id'] }}">
                            <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 p-2 rounded-lg transition" title="حذف هذا القسم">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

    </main>

    <!-- Modal Form (Hidden by default) -->
    <div id="category-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"></div>

        <!-- Modal Wrapper -->
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-right shadow-2xl transition-all duration-300 w-full max-w-2xl p-6 md:p-8 my-8 border border-white/20">
                
                <!-- Modal Header -->
                <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2" id="modal-title">
                        <i class="fa-solid fa-folder-plus text-primary"></i>
                        <span>إضافة قسم جديد</span>
                    </h3>
                    <button type="button" id="btn-close-modal" class="text-slate-400 hover:text-slate-600 transition text-xl p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Modal Body (Form) -->
                <form action="{{ route('settings.categories.save') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="original_id" id="field-original-id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- المعرف بالإنجليزية (ID) -->
                        <div>
                            <label for="field-id" class="block text-xs font-bold text-slate-600 mb-2">المعرف الفرعي بالإنجليزية (ID / Slug)</label>
                            <input type="text" name="id" id="field-id" required placeholder="مثال: books"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none font-mono">
                            <span class="text-[10px] text-slate-400 block mt-1">يجب أن يتكون من أحرف إنجليزية صغيرة وأرقام وعلامات ربط فقط دون مسافات.</span>
                        </div>
                        <!-- الاسم الظاهر -->
                        <div>
                            <label for="field-name" class="block text-xs font-bold text-slate-600 mb-2">اسم القسم (الظاهر للزوار)</label>
                            <input type="text" name="name" id="field-name" required placeholder="مثال: الكتب التعليمية والمراجع"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- الأيقونة -->
                        <div>
                            <label for="field-icon" class="block text-xs font-bold text-slate-600 mb-2">أيقونة القسم (FontAwesome class)</label>
                            <div class="relative">
                                <input type="text" name="icon" id="field-icon" required placeholder="fa-solid fa-book"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none font-mono">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">
                                    <i id="field-icon-preview" class="fa-solid fa-folder"></i>
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-400 block mt-1">أمثلة: <code>fa-solid fa-book-open</code>, <code>fa-solid fa-video</code>, <code>fa-solid fa-file-pdf</code></span>
                        </div>
                        <!-- التخطيط -->
                        <div>
                            <label for="field-layout" class="block text-xs font-bold text-slate-600 mb-2">طريقة العرض وتخطيط الملفات</label>
                            <select name="layout" id="field-layout" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none bg-white">
                                <option value="video">مشغل فيديو (مخصص للمحاضرات المرئية والـ MP4)</option>
                                <option value="document">مستندات (PDF قراءة مباشرة، والـ Office تنزيل)</option>
                                <option value="download">تحميل مباشر فقط (للبرامج والأدوات والملفات المضغوطة)</option>
                            </select>
                        </div>
                    </div>

                    <!-- مسار السيرفر -->
                    <div>
                        <label for="field-path" class="block text-xs font-bold text-slate-600 mb-2">المسار الكامل للمجلد على السيرفر</label>
                        <input type="text" name="path" id="field-path" required placeholder="مثال: E:/courses/books"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none font-mono">
                        <span class="text-[10px] text-slate-400 block mt-1">المسار الفعلي الكامل على السيرفر، يدعم المجلدات المحلية والخارجية.</span>
                    </div>

                    <!-- صورة القسم -->
                    <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-200/50 space-y-4 font-sans">
                        <label class="block text-xs font-bold text-slate-700">صورة أو غلاف القسم (اختياري)</label>
                        <div class="flex items-center gap-4">
                            <!-- مكان عرض معاينة الصورة الحالية -->
                            <div id="image-preview-container" class="w-16 h-16 rounded-xl border border-slate-200 overflow-hidden bg-slate-100 flex items-center justify-center flex-shrink-0 shadow-inner">
                                <span class="text-[10px] text-slate-400 text-center">لا توجد صورة</span>
                            </div>
                            
                            <div class="flex-grow space-y-2">
                                <!-- زر اختيار الملف -->
                                <input type="file" name="image_file" id="field-image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-0 file:py-1.5 file:px-3 file:rounded-xl file:border file:border-slate-200 file:text-xs file:font-semibold file:bg-white file:text-slate-700 hover:file:bg-slate-50 file:cursor-pointer">
                                
                                <!-- خيار حذف الصورة والعودة للأيقونة -->
                                <label id="remove-image-wrapper" class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-rose-600 font-bold hidden">
                                    <input type="checkbox" name="remove_image" id="field-remove-image" value="1" class="text-rose-500 focus:ring-rose-500 rounded border-slate-300">
                                    <span>حذف الصورة الحالية والعودة لاستخدام الأيقونة</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- قائمة تشيك ليست للامتدادات المسموح بها -->
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 space-y-4">
                        <h4 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-accent"></i>
                            الامتدادات والصيغ المسموح بعرضها في هذا القسم
                        </h4>
                        
                        <div class="space-y-5">
                            @foreach($availableExtensions as $group => $exts)
                                <div class="space-y-2 border-b border-slate-200/60 pb-3 last:border-0 last:pb-0">
                                    <div class="flex justify-between items-center">
                                        <span class="text-[11px] font-bold text-primary">{{ $group }}</span>
                                        <button type="button" class="text-[10px] text-slate-400 hover:text-accent font-bold select-group-btn" data-group-exts="{{ implode(',', $exts) }}">تحديد الكل</button>
                                    </div>
                                    <div class="flex flex-wrap gap-2.5">
                                        @foreach($exts as $ext)
                                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold cursor-pointer hover:border-accent hover:bg-slate-50/50 transition">
                                                <input type="checkbox" name="extensions[]" value="{{ $ext }}" class="ext-checkbox text-accent focus:ring-accent rounded border-slate-300">
                                                <span>.{{ strtoupper($ext) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Submit Controls -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" id="btn-cancel-modal" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-sm">
                            إلغاء
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-primary hover:opacity-95 text-white font-bold rounded-xl transition text-sm shadow-md">
                            حفظ القسم والتغييرات
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('category-modal');
            const btnAddCategory = document.getElementById('btn-add-category');
            const btnCloseModal = document.getElementById('btn-close-modal');
            const btnCancelModal = document.getElementById('btn-cancel-modal');
            const modalTitle = document.getElementById('modal-title');
            
            // Form Fields
            const fieldOriginalId = document.getElementById('field-original-id');
            const fieldId = document.getElementById('field-id');
            const fieldName = document.getElementById('field-name');
            const fieldIcon = document.getElementById('field-icon');
            const fieldIconPreview = document.getElementById('field-icon-preview');
            const fieldPath = document.getElementById('field-path');
            const fieldLayout = document.getElementById('field-layout');
            const extCheckboxes = document.querySelectorAll('.ext-checkbox');

            // Select Group button
            const selectGroupBtns = document.querySelectorAll('.select-group-btn');

            // Open Modal in Create mode
            btnAddCategory.addEventListener('click', function() {
                resetForm();
                modalTitle.innerHTML = `<i class="fa-solid fa-folder-plus text-primary"></i> <span>إضافة قسم جديد</span>`;
                openModal();
            });

            // Open Modal in Edit mode
            document.querySelectorAll('.btn-edit-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    const cat = JSON.parse(this.dataset.category);
                    resetForm();
                    
                    modalTitle.innerHTML = `<i class="fa-solid fa-pen-to-square text-primary"></i> <span>تعديل بيانات القسم: ${cat.name}</span>`;
                    
                    fieldOriginalId.value = cat.id;
                    fieldId.value = cat.id;
                    fieldName.value = cat.name;
                    fieldIcon.value = cat.icon;
                    fieldIconPreview.className = cat.icon || 'fa-solid fa-folder';
                    fieldPath.value = cat.path;
                    fieldLayout.value = cat.layout || 'video';

                    // Check extensions
                    const activeExts = Array.isArray(cat.extensions) ? cat.extensions : [];
                    extCheckboxes.forEach(chk => {
                        if (activeExts.includes(chk.value.toLowerCase())) {
                            chk.checked = true;
                            chk.closest('label').classList.add('border-accent', 'bg-accent/5');
                        }
                    });

                    // Image handle
                    const imagePreviewContainer = document.getElementById('image-preview-container');
                    const removeImageWrapper = document.getElementById('remove-image-wrapper');
                    const fieldRemoveImage = document.getElementById('field-remove-image');
                    
                    if (cat.image_path) {
                        imagePreviewContainer.innerHTML = `<img src="${window.location.origin}/${cat.image_path}" class="w-full h-full object-cover">`;
                        removeImageWrapper.classList.remove('hidden');
                    } else {
                        imagePreviewContainer.innerHTML = `<span class="text-[10px] text-slate-400 text-center">لا توجد صورة</span>`;
                        removeImageWrapper.classList.add('hidden');
                    }
                    fieldRemoveImage.checked = false;

                    openModal();
                });
            });

            // Select All/Deselect All group logic
            selectGroupBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const extsStr = this.dataset.groupExts;
                    const exts = extsStr.split(',');
                    
                    // Determine if all are currently checked
                    let allChecked = true;
                    extCheckboxes.forEach(chk => {
                        if (exts.includes(chk.value)) {
                            if (!chk.checked) allChecked = false;
                        }
                    });

                    // Toggle status
                    extCheckboxes.forEach(chk => {
                        if (exts.includes(chk.value)) {
                            chk.checked = !allChecked;
                            const label = chk.closest('label');
                            if (chk.checked) {
                                label.classList.add('border-accent', 'bg-accent/5');
                            } else {
                                label.classList.remove('border-accent', 'bg-accent/5');
                            }
                        }
                    });

                    // Update button text
                    this.textContent = allChecked ? 'تحديد الكل' : 'إلغاء التحديد';
                });
            });

            // Dynamic Icon indicator preview
            fieldIcon.addEventListener('input', function() {
                fieldIconPreview.className = this.value || 'fa-solid fa-folder';
            });

            // Dynamic Image File preview
            const fieldImage = document.getElementById('field-image');
            const imagePreviewContainer = document.getElementById('image-preview-container');
            
            fieldImage.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreviewContainer.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    }
                    reader.readAsDataURL(file);
                } else {
                    imagePreviewContainer.innerHTML = `<span class="text-[10px] text-slate-400 text-center">لا توجد صورة</span>`;
                }
            });

            // Styling dynamic checkboxes on change
            extCheckboxes.forEach(chk => {
                chk.addEventListener('change', function() {
                    const label = this.closest('label');
                    if (this.checked) {
                        label.classList.add('border-accent', 'bg-accent/5');
                    } else {
                        label.classList.remove('border-accent', 'bg-accent/5');
                    }
                });
            });

            // Helpers
            function openModal() {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            function resetForm() {
                fieldOriginalId.value = '';
                fieldId.value = '';
                fieldName.value = '';
                fieldIcon.value = 'fa-solid fa-folder';
                fieldIconPreview.className = 'fa-solid fa-folder';
                fieldPath.value = '';
                fieldLayout.value = 'document';
                
                // Reset image preview
                const imagePreviewContainer = document.getElementById('image-preview-container');
                const removeImageWrapper = document.getElementById('remove-image-wrapper');
                const fieldRemoveImage = document.getElementById('field-remove-image');
                const fieldImage = document.getElementById('field-image');
                
                imagePreviewContainer.innerHTML = `<span class="text-[10px] text-slate-400 text-center">لا توجد صورة</span>`;
                removeImageWrapper.classList.add('hidden');
                fieldRemoveImage.checked = false;
                fieldImage.value = '';

                extCheckboxes.forEach(chk => {
                    chk.checked = false;
                    chk.closest('label').classList.remove('border-accent', 'bg-accent/5');
                });

                selectGroupBtns.forEach(btn => {
                    btn.textContent = 'تحديد الكل';
                });
            }

            btnCloseModal.addEventListener('click', closeModal);
            btnCancelModal.addEventListener('click', closeModal);
        });
    </script>
</body>
</html>
