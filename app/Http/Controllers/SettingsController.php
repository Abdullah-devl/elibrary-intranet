<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;

class SettingsController extends Controller
{
    /**
     * عرض صفحة تسجيل دخول المشرف
     */
    public function showLogin()
    {
        // إذا كان مسجلاً الدخول بالفعل، نقله للإعدادات مباشرة
        if (session('admin_authenticated')) {
            return redirect()->route('settings.index');
        }
        return view('login');
    }

    /**
     * معالجة طلب تسجيل الدخول بالبريد الإلكتروني وكلمة المرور
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $settingsPath = storage_path('app/settings.json');
        
        // جلب الإعدادات الافتراضية
        $defaultEmail = 'admin@gmail.com';
        $defaultPasswordHash = Hash::make('12345678');
        
        $storedEmail = $defaultEmail;
        $storedPasswordHash = $defaultPasswordHash;

        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true);
            $storedEmail = $settings['admin_email'] ?? $defaultEmail;
            $storedPasswordHash = $settings['admin_password'] ?? $defaultPasswordHash;
        }

        // تحقق من التطابق التام
        if ($request->input('email') === $storedEmail && Hash::check($request->input('password'), $storedPasswordHash)) {
            session(['admin_authenticated' => true]);
            return redirect()->route('settings.index')->with('success', 'تم تسجيل الدخول بنجاح!');
        }

        return redirect()->back()->with('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة!')->withInput($request->only('email'));
    }

    /**
     * تسجيل الخروج وإنهاء الجلسة
     */
    public function logout()
    {
        session()->forget('admin_authenticated');
        return redirect('/')->with('success', 'تم تسجيل الخروج بنجاح!');
    }

    /**
     * عرض صفحة الإعدادات (محمي بالتحقق)
     */
    public function index()
    {
        if (!session('admin_authenticated')) {
            return redirect()->route('settings.login');
        }

        $settingsPath = storage_path('app/settings.json');
        $settings = [];
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?: [];
        }

        // قراءة حالة الأقسام ديناميكياً
        $categories = $settings['categories'] ?? [];
        $pathsStatus = [];
        $categoryNames = [];
        foreach ($categories as $cat) {
            $pathsStatus[$cat['id']] = File::exists($cat['path'] ?? '');
            $categoryNames[$cat['id']] = $cat['name'] ?? $cat['id'];
        }

        // جلب الإحصائيات العامة
        $totalVisits = ActivityLog::where('log_type', 'visit')->count();
        $totalDownloads = ActivityLog::where('log_type', 'download')->count();
        $totalCopies = ActivityLog::where('log_type', 'copy_to_drive')->count();

        // أكثر الملفات تحميلاً وتشغيلاً (التحميل + النسخ)
        $topDownloads = ActivityLog::whereIn('log_type', ['download', 'copy_to_drive'])
            ->select('file_path', 'file_name', 'category_id', DB::raw('count(*) as total_count'))
            ->groupBy('file_path', 'file_name', 'category_id')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get();

        // الزيارات حسب الأقسام
        $visitsByCategory = ActivityLog::where('log_type', 'visit')
            ->select('category_id', DB::raw('count(*) as total_visits'))
            ->groupBy('category_id')
            ->orderByDesc('total_visits')
            ->get();

        return view('settings', compact(
            'pathsStatus',
            'totalVisits',
            'totalDownloads',
            'totalCopies',
            'topDownloads',
            'visitsByCategory',
            'categoryNames'
        ));
    }

    /**
     * معالجة تحديث الإعدادات وحماية الحساب (محمي بالتحقق)
     */
    public function update(Request $request)
    {
        if (!session('admin_authenticated')) {
            return redirect()->route('settings.login');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'logo_type' => 'required|in:icon,image',
            'logo_icon' => 'nullable|string|max:50',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'org_logo_type' => 'required|in:icon,image',
            'org_logo_icon' => 'nullable|string|max:50',
            'org_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'color_primary' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_accent' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_bglight' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'welcome_text' => 'required|string|max:1000',
            // حماية حساب المشرف
            'admin_email' => 'required|email|max:100',
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|confirmed|min:8|string', // الحد الأدنى 8 خانات
            // إعدادات الأداء الجديدة
            'cache_duration' => 'required|integer|min:0|max:1440',
            'file_serving_mode' => 'required|in:php,x_sendfile,x_accel_redirect',
            'nginx_internal_path' => 'nullable|string|max:255',
        ], [
            'admin_email.required' => 'حقل البريد الإلكتروني للمشرف مطلوب.',
            'admin_email.email' => 'يرجى كتابة بريد إلكتروني بشكل صحيح.',
            'new_password.min' => 'يجب ألا تقل كلمة المرور الجديدة عن 8 أحرف/أرقام.',
            'new_password.confirmed' => 'تأكيد كلمة المرور الجديدة غير متطابق.',
        ]);

        $settingsPath = storage_path('app/settings.json');
        
        // قراءة الإعدادات الحالية
        $settings = [];
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?: [];
        }

        // التحقق وتحديث كلمة المرور إذا طلبت
        if ($request->filled('new_password')) {
            $storedPasswordHash = $settings['admin_password'] ?? Hash::make('12345678');
            
            if (!Hash::check($request->input('current_password'), $storedPasswordHash)) {
                return redirect()->back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة!'])->withInput();
            }

            $settings['admin_password'] = Hash::make($request->input('new_password'));
        }

        // تحديث باقي الحقول
        $settings['name'] = $request->input('name');
        $settings['logo_type'] = $request->input('logo_type');
        $settings['logo_icon'] = $request->input('logo_icon') ?: 'fa-solid fa-graduation-cap';
        $settings['org_logo_type'] = $request->input('org_logo_type');
        $settings['org_logo_icon'] = $request->input('org_logo_icon') ?: 'fa-solid fa-building';
        $settings['color_primary'] = $request->input('color_primary');
        $settings['color_accent'] = $request->input('color_accent');
        $settings['color_bglight'] = $request->input('color_bglight');
        $settings['admin_email'] = $request->input('admin_email');
        $settings['welcome_text'] = $request->input('welcome_text');
        
        // تخزين إعدادات الأداء الجديدة
        $settings['cache_duration'] = (int) $request->input('cache_duration');
        $settings['file_serving_mode'] = $request->input('file_serving_mode');
        $settings['nginx_internal_path'] = $request->input('nginx_internal_path') ?: '/protected-files';

        // تنظيف وحذف المفاتيح القديمة غير المستخدمة
        unset($settings['path_videos']);
        unset($settings['path_books']);
        unset($settings['path_programs']);

        // التعامل مع رفع الشعار
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $uploadPath = public_path('uploads');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            if (!empty($settings['logo_path'])) {
                $oldLogoPath = public_path($settings['logo_path']);
                if (File::exists($oldLogoPath)) {
                    File::delete($oldLogoPath);
                }
            }

            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            $settings['logo_path'] = 'uploads/' . $filename;
        }

        if ($request->hasFile('org_logo_file')) {
            $file = $request->file('org_logo_file');
            $uploadPath = public_path('uploads');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            if (!empty($settings['org_logo_path'])) {
                $oldLogoPath = public_path($settings['org_logo_path']);
                if (File::exists($oldLogoPath)) {
                    File::delete($oldLogoPath);
                }
            }

            $filename = 'org_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            $settings['org_logo_path'] = 'uploads/' . $filename;
        }

        // حفظ ملف الإعدادات
        File::ensureDirectoryExists(dirname($settingsPath));
        File::put($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // تفريغ الكاش بالكامل للتأكد من تطبيق الإعدادات البرمجية الجديدة فوراً
        Cache::flush();

        return redirect()->back()->with('success', 'تم حفظ الإعدادات بنجاح وتحديث الذاكرة المؤقتة!');
    }

    /**
     * عرض صفحة إدارة الأقسام
     */
    public function categoriesIndex()
    {
        if (!session('admin_authenticated')) {
            return redirect()->route('settings.login');
        }

        $settingsPath = storage_path('app/settings.json');
        $settings = [];
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?: [];
        }

        $categories = $settings['categories'] ?? [];
        $pathsStatus = [];
        foreach ($categories as $cat) {
            $pathsStatus[$cat['id']] = File::exists($cat['path'] ?? '');
        }

        // الامتدادات الشائعة المجمعة
        $availableExtensions = [
            'المرئيات والفيديو' => ['mp4', 'webm', 'mkv', 'avi', 'mov', 'flv', 'wmv', '3gp'],
            'الكتب والمستندات' => ['pdf', 'epub', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'rtf', 'odt'],
            'البرامج والملفات المضغوطة' => ['exe', 'msi', 'zip', 'rar', '7z', 'tar', 'gz', 'iso', 'dmg', 'apk'],
            'الملفات الصوتية' => ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac'],
            'الصور والتصاميم' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico']
        ];

        return view('categories', compact('pathsStatus', 'categories', 'availableExtensions'));
    }

    /**
     * حفظ (إنشاء أو تحديث) قسم واحد
     */
    public function categoriesSave(Request $request)
    {
        if (!session('admin_authenticated')) {
            return redirect()->route('settings.login');
        }

        $request->validate([
            'id' => 'required|string|alpha_dash|max:50',
            'name' => 'required|string|max:100',
            'icon' => 'required|string|max:100',
            'path' => 'required|string|max:255',
            'layout' => 'required|in:video,document,download',
            'extensions' => 'required|array|min:1',
            'extensions.*' => 'required|string|max:30',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'id.required' => 'معرف القسم مطلوب.',
            'id.alpha_dash' => 'يجب أن يحتوي معرف القسم على حروف إنجليزية، أرقام، وشرطات فقط.',
            'name.required' => 'اسم القسم مطلوب.',
            'icon.required' => 'أيقونة القسم مطلوبة.',
            'path.required' => 'مسار المجلد على السيرفر مطلوب.',
            'layout.required' => 'طريقة العرض والتخطيط مطلوبة.',
            'extensions.required' => 'يجب اختيار امتداد واحد على الأقل.',
            'extensions.min' => 'يجب اختيار امتداد واحد على الأقل.',
            'image_file.image' => 'يجب اختيار صورة صالحة.',
            'image_file.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.',
        ]);

        $settingsPath = storage_path('app/settings.json');
        $settings = [];
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?: [];
        }

        $categories = $settings['categories'] ?? [];
        
        $originalId = $request->input('original_id');
        $id = strtolower(trim($request->input('id')));
        $id = str_replace(' ', '-', $id);

        $newCategory = [
            'id' => $id,
            'name' => trim($request->input('name')),
            'icon' => trim($request->input('icon')),
            'path' => str_replace('\\', '/', trim($request->input('path'))),
            'layout' => $request->input('layout'),
            'extensions' => array_values(array_unique(array_map('strtolower', $request->input('extensions')))),
            'image_path' => '', // القيمة الافتراضية
        ];

        // التعامل مع خيار حذف الصورة القديمة
        $removeImage = $request->boolean('remove_image');

        // جلب الصورة القديمة إن وجدت
        $oldImagePath = '';
        if (!empty($originalId)) {
            foreach ($categories as $cat) {
                if ($cat['id'] === $originalId) {
                    $oldImagePath = $cat['image_path'] ?? '';
                    $newCategory['image_path'] = $oldImagePath;
                    break;
                }
            }
        }

        if ($removeImage) {
            if (!empty($oldImagePath)) {
                $fullOldPath = public_path($oldImagePath);
                if (File::exists($fullOldPath)) {
                    File::delete($fullOldPath);
                }
            }
            $newCategory['image_path'] = '';
        }

        // رفع الصورة الجديدة وحفظها
        if ($request->hasFile('image_file')) {
            // حذف الصورة القديمة أولاً لتجنب تراكم الملفات
            if (!empty($oldImagePath)) {
                $fullOldPath = public_path($oldImagePath);
                if (File::exists($fullOldPath)) {
                    File::delete($fullOldPath);
                }
            }

            $file = $request->file('image_file');
            $uploadPath = public_path('uploads');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            $filename = 'cat_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            $newCategory['image_path'] = 'uploads/' . $filename;
        }

        // تحقق من التكرار إذا كان معرفاً جديداً بالكامل
        if (empty($originalId)) {
            // إضافة قسم جديد
            foreach ($categories as $cat) {
                if ($cat['id'] === $id) {
                    return redirect()->back()->withErrors(['id' => 'معرف القسم هذا مستخدم بالفعل، يرجى كتابة معرف فريد.'])->withInput();
                }
            }
            $categories[] = $newCategory;
        } else {
            // تحديث قسم موجود
            $found = false;
            foreach ($categories as $index => $cat) {
                if ($cat['id'] === $originalId) {
                    // تحقق من المعرف الجديد هل هو مكرر مع قسم آخر
                    if ($id !== $originalId) {
                        foreach ($categories as $otherCat) {
                            if ($otherCat['id'] === $id) {
                                return redirect()->back()->withErrors(['id' => 'معرف القسم الجديد مستخدم بالفعل في قسم آخر.'])->withInput();
                            }
                        }
                    }
                    $categories[$index] = $newCategory;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $categories[] = $newCategory;
            }
        }

        $settings['categories'] = $categories;

        File::ensureDirectoryExists(dirname($settingsPath));
        File::put($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // تفريغ الكاش لضمان تحديث قوائم الطلاب فوراً
        Cache::flush();

        return redirect()->route('settings.categories.index')->with('success', 'تم حفظ القسم بنجاح!');
    }

    /**
     * حذف قسم
     */
    public function categoriesDelete(Request $request)
    {
        if (!session('admin_authenticated')) {
            return redirect()->route('settings.login');
        }

        $request->validate([
            'id' => 'required|string|max:50',
        ]);

        $settingsPath = storage_path('app/settings.json');
        $settings = [];
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?: [];
        }

        $categories = $settings['categories'] ?? [];
        $id = $request->input('id');

        if (count($categories) <= 1) {
            return redirect()->back()->with('error', 'يجب أن تحتوي المكتبة على قسم واحد على الأقل. لا يمكن حذف هذا القسم.');
        }

        $newCategories = [];
        foreach ($categories as $cat) {
            if ($cat['id'] !== $id) {
                $newCategories[] = $cat;
            } else {
                // حذف الصورة المرفقة بالقسم من القرص إن وجدت
                if (!empty($cat['image_path'])) {
                    $fullOldPath = public_path($cat['image_path']);
                    if (File::exists($fullOldPath)) {
                        File::delete($fullOldPath);
                    }
                }
            }
        }

        $settings['categories'] = $newCategories;

        File::ensureDirectoryExists(dirname($settingsPath));
        File::put($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // تفريغ الكاش لضمان تحديث قوائم الطلاب فوراً
        Cache::flush();

        return redirect()->route('settings.categories.index')->with('success', 'تم حذف القسم بنجاح!');
    }
}
