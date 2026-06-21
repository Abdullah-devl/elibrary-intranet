<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        // 1. قراءة إعدادات المسارات من ملف الإعدادات
        $settingsPath = storage_path('app/settings.json');
        $settings = [];
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?: [];
        }

        $categories = $settings['categories'] ?? [];
        if (empty($categories)) {
            // في حال عدم وجود أي قسم، نستخدم الأقسام الافتراضية
            $categories = [
                [
                    'id' => 'videos',
                    'name' => 'المحاضرات المرئية',
                    'icon' => 'fa-solid fa-film',
                    'path' => public_path('videos'),
                    'extensions' => ['mp4', 'webm', 'mkv', 'avi'],
                    'layout' => 'video'
                ]
            ];
        }

        // 2. تحديد نوع القسم النشط
        $type = $request->query('type');
        
        // البحث عن القسم النشط
        $currentCategory = null;
        foreach ($categories as $cat) {
            if ($cat['id'] === $type) {
                $currentCategory = $cat;
                break;
            }
        }

        // إذا لم يتم تحديد القسم أو كان غير صالح، نأخذ أول قسم
        if (!$currentCategory) {
            $currentCategory = $categories[0];
            $type = $currentCategory['id'];
        }

        // 3. تحديد المسار الأساسي الفعلي
        $basePath = $currentCategory['path'] ?? public_path($type);

        // التأكد من وجود المجلد الأساسي، وإذا لم يكن موجوداً نحاول إنشائه أو استخدام الافتراضي
        if (!File::exists($basePath)) {
            try {
                File::makeDirectory($basePath, 0755, true, true);
            } catch (\Exception $e) {
                // في حال فشل الإنشاء نستخدم المجلد العام الافتراضي
                $basePath = public_path($type);
                if (!File::exists($basePath)) {
                    File::makeDirectory($basePath, 0755, true, true);
                }
            }
        }

        // 4. معرفة المجلد الحالي المطلوب تصفحه
        $currentFolder = $request->query('folder', '');
        
        // تنظيف مسار المجلد الحالي لحماية النظام من التنقل خارج المسار المسموح به
        $currentFolder = str_replace(['..', '\\'], ['', '/'], $currentFolder);
        $currentFolder = trim($currentFolder, '/');

        // 5. دمج المسار الأساسي مع المجلد الحالي
        $fullPath = empty($currentFolder) ? $basePath : $basePath . '/' . $currentFolder;

        // التحقق من وجود المجلد المطلوب تصفحه
        $folders = [];
        $files = [];

        if (File::exists($fullPath) && File::isDirectory($fullPath)) {
            // جلب المجلدات الفرعية
            $directories = File::directories($fullPath);
            $folders = array_map('basename', $directories);

            // جلب الملفات وتصفيتها حسب امتدادات القسم المحدد
            $allFiles = File::files($fullPath);
            
            $allowedExtensions = $currentCategory['extensions'] ?? [];
            // تحويل الامتدادات إلى أحرف صغيرة للمقارنة الصحيحة
            $allowedExtensions = array_map('strtolower', $allowedExtensions);

            foreach ($allFiles as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $allowedExtensions)) {
                    $files[] = [
                        'name' => $file->getFilename(),
                        'name_without_ext' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                        'extension' => $ext,
                        'size' => $this->formatBytes($file->getSize())
                    ];
                }
            }
        }

        return view('welcome', compact('folders', 'files', 'currentFolder', 'type', 'currentCategory'));
    }

    /**
     * تشغيل وتحميل الملفات بشكل آمن من أي مكان على السيرفر
     */
    public function serveFile(Request $request)
    {
        $type = $request->query('type', 'videos');
        $file = $request->query('file');

        if (empty($file)) {
            abort(400, 'اسم الملف مطلوب.');
        }

        // تنظيف المعامل لمنع الهجمات
        $file = str_replace(['..', '\\'], ['', '/'], $file);
        $file = trim($file, '/');

        $settingsPath = storage_path('app/settings.json');
        $settings = [];
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?: [];
        }

        $categories = $settings['categories'] ?? [];
        $currentCategory = null;
        foreach ($categories as $cat) {
            if ($cat['id'] === $type) {
                $currentCategory = $cat;
                break;
            }
        }

        if (!$currentCategory) {
            abort(404, 'القسم غير موجود.');
        }

        $basePath = $currentCategory['path'] ?? public_path($type);

        $realBase = realpath($basePath);
        if (!$realBase) {
            abort(404, 'المجلد الأساسي غير موجود أو غير صالح.');
        }
        $realBaseDir = rtrim($realBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // دمج المسارات بدقة
        $fullPath = $realBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);

        // فحص أمني لمنع هجمات تخطي المجلدات (Directory Traversal)
        if (!str_starts_with(realpath($fullPath) ?: $fullPath, $realBaseDir)) {
            abort(403, 'غير مصرح بالوصول لهذا المسار.');
        }

        if (!File::exists($fullPath) || File::isDirectory($fullPath)) {
            abort(404, 'الملف غير موجود.');
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $allowedExtensions = $currentCategory['extensions'] ?? [];
        $allowedExtensions = array_map('strtolower', $allowedExtensions);

        if (!in_array($extension, $allowedExtensions)) {
            abort(403, 'صيغة الملف غير مصرح بها.');
        }

        // تقديم الفيديو والـ PDF والـ TXT والصور للعرض، وباقي الصيغ للتحميل
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'];
        if (($currentCategory['layout'] ?? '') === 'video' || 
            in_array($extension, ['pdf', 'txt']) || 
            in_array($extension, $imageExtensions)) {
            return response()->file($fullPath);
        } else {
            return response()->download($fullPath);
        }
    }

    /**
     * تحويل حجم الملفات لصيغة مقروءة
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}