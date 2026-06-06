<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

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

        $pathsStatus = [
            'videos' => File::exists($settings['path_videos'] ?? public_path('videos')),
            'books' => File::exists($settings['path_books'] ?? public_path('books')),
            'programs' => File::exists($settings['path_programs'] ?? public_path('programs')),
        ];

        return view('settings', compact('pathsStatus'));
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
            'color_primary' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_accent' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_bglight' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'path_videos' => 'required|string|max:255',
            'path_books' => 'required|string|max:255',
            'path_programs' => 'required|string|max:255',
            'welcome_text' => 'required|string|max:1000',
            // حماية حساب المشرف
            'admin_email' => 'required|email|max:100',
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|confirmed|min:8|string', // الحد الأدنى 8 خانات
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
        $settings['color_primary'] = $request->input('color_primary');
        $settings['color_accent'] = $request->input('color_accent');
        $settings['color_bglight'] = $request->input('color_bglight');
        $settings['path_videos'] = $request->input('path_videos');
        $settings['path_books'] = $request->input('path_books');
        $settings['path_programs'] = $request->input('path_programs');
        $settings['admin_email'] = $request->input('admin_email');
        $settings['welcome_text'] = $request->input('welcome_text');

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

        // حفظ ملف الإعدادات
        File::ensureDirectoryExists(dirname($settingsPath));
        File::put($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return redirect()->back()->with('success', 'تم حفظ الإعدادات بنجاح!');
    }
}
