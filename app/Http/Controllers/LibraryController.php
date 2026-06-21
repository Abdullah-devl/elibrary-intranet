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
                        'size' => $this->formatBytes($file->getSize()),
                        'size_bytes' => $file->getSize()
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

    /**
     * الكشف عن الأقراص الموصولة بالسيرفر
     */
    public function detectDrives()
    {
        $drives = [];
        
        // التحقق من أن الخادم يعمل بنظام التشغيل ويندوز
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || PHP_OS_FAMILY === 'Windows') {
            // فحص حروف الأقراص من D إلى Z (تخطي C لحماية النظام)
            foreach (range('D', 'Z') as $letter) {
                $path = "$letter:\\";
                if (is_dir($path) && is_writable($path)) {
                    $free = @disk_free_space($path);
                    $total = @disk_total_space($path);
                    
                    // محاولة جلب اسم القرص (Volume Name)
                    $volumeName = '';
                    try {
                        $output = shell_exec("wmic logicaldisk where DeviceID='{$letter}:' get VolumeName 2>&1");
                        if ($output) {
                            $lines = explode("\n", trim($output));
                            if (isset($lines[1])) {
                                $volumeName = trim($lines[1]);
                            }
                        }
                    } catch (\Exception $e) {
                        $volumeName = '';
                    }
                    
                    $drives[] = [
                        'letter' => $letter,
                        'path' => $path,
                        'volume_name' => $volumeName ?: 'قرص محلي',
                        'free_bytes' => $free,
                        'free' => $free ? $this->formatBytes($free) : 'غير معروف',
                        'total' => $total ? $this->formatBytes($total) : 'غير معروف',
                        'percent_used' => ($total && $free) ? round((($total - $free) / $total) * 100) : 0
                    ];
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'drives' => $drives
        ]);
    }

    /**
     * نسخ ملف من السيرفر إلى القرص المحدد
     */
    public function copyToDrive(\Illuminate\Http\Request $request)
    {
        $type = $request->input('type');
        $file = $request->input('file');
        $driveLetter = strtoupper(trim($request->input('drive')));

        if (empty($file) || empty($driveLetter)) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الطلب غير مكتملة.'
            ], 400);
        }

        // 1. تنظيف حرف القرص والتحقق منه
        if (!preg_match('/^[D-Z]$/', $driveLetter)) {
            return response()->json([
                'success' => false,
                'message' => 'حرف القرص غير صالح أو غير مسموح بالنسخ إليه.'
            ], 400);
        }

        $destDrivePath = "{$driveLetter}:\\";
        if (!is_dir($destDrivePath) || !is_writable($destDrivePath)) {
            return response()->json([
                'success' => false,
                'message' => "القرص ({$driveLetter}:\\) غير متوفر حالياً أو غير قابل للكتابة."
            ], 400);
        }

        // 2. تنظيف مسار الملف وجلب إعدادات القسم للتحقق الأمني
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
            return response()->json([
                'success' => false,
                'message' => 'القسم المطلوب غير موجود.'
            ], 404);
        }

        $basePath = $currentCategory['path'] ?? public_path($type);
        $realBase = realpath($basePath);
        if (!$realBase) {
            return response()->json([
                'success' => false,
                'message' => 'المجلد الأساسي للقسم غير صالح.'
            ], 404);
        }
        $realBaseDir = rtrim($realBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // دمج المسارات بدقة
        $fullPath = $realBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);

        // فحص أمني لمنع هجمات تخطي المجلدات (Directory Traversal)
        if (!str_starts_with(realpath($fullPath) ?: $fullPath, $realBaseDir)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بالوصول لهذا الملف.'
            ], 403);
        }

        if (!File::exists($fullPath) || File::isDirectory($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'الملف غير موجود على السيرفر.'
            ], 404);
        }

        // التحقق من الصيغة المسموح بها للقسم
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $allowedExtensions = $currentCategory['extensions'] ?? [];
        $allowedExtensions = array_map('strtolower', $allowedExtensions);

        if (!in_array($extension, $allowedExtensions)) {
            return response()->json([
                'success' => false,
                'message' => 'صيغة الملف غير مسموح بها في هذا القسم.'
            ], 403);
        }

        // 3. التحقق من المساحة الفارغة في القرص المستهدف
        $fileSize = File::size($fullPath);
        $freeSpace = @disk_free_space($destDrivePath);

        if ($freeSpace !== false && $freeSpace < $fileSize) {
            return response()->json([
                'success' => false,
                'message' => 'المساحة المتوفرة على القرص غير كافية لنسخ هذا الملف.'
            ], 400);
        }

        // 4. نسخ الملف الفعلي
        $fileName = basename($fullPath);
        $destinationPath = $destDrivePath . $fileName;

        try {
            if (copy($fullPath, $destinationPath)) {
                return response()->json([
                    'success' => true,
                    'message' => "تم نسخ الملف بنجاح إلى الفلاش ميموري ({$destinationPath})."
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشلت عملية نسخ الملف للقرص، يرجى التحقق من الصلاحيات.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع أثناء عملية النسخ: ' . $e->getMessage()
            ], 500);
        }
    }
}