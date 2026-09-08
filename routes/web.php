<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\SettingsController;

// الصفحة الترحيبية للزوار
Route::get('/', function () {
    return view('landing');
});

// تصفح المكتبة التعليمية
Route::get('/library', [LibraryController::class, 'index'])->name('library.index');

// مسار تشغيل وتحميل الملفات بشكل آمن من أي مجلد على السيرفر
Route::get('/serve-file', [LibraryController::class, 'serveFile'])->name('file.serve');

// مسارات كشف الأقراص والنسخ المباشر للفلاش ميموري
Route::get('/library/detect-drives', [LibraryController::class, 'detectDrives'])->name('library.detect_drives');
// حماية مسار النسخ للفلاش من ثغرة DoS بتحديد 60 طلب في الدقيقة
Route::post('/library/copy-to-drive', [LibraryController::class, 'copyToDrive'])->middleware('throttle:60,1')->name('library.copy_to_drive');

// مسارات صفحة الإعدادات والحماية
Route::get('/settings/login', [SettingsController::class, 'showLogin'])->name('settings.login');
// تطبيق قيد على المحاولات (5 محاولات في الدقيقة) لمنع هجمات القوة الغاشمة (Brute Force)
Route::post('/settings/login', [SettingsController::class, 'login'])->middleware('throttle:5,1')->name('settings.login_submit');
Route::post('/settings/logout', [SettingsController::class, 'logout'])->name('settings.logout');

// حماية جميع مسارات الإعدادات باستخدام الـ Middleware الأمني الجديد (admin.auth)
Route::middleware(['admin.auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // مسارات إدارة الأقسام ديناميكياً
    Route::get('/settings/categories', [SettingsController::class, 'categoriesIndex'])->name('settings.categories.index');
    Route::post('/settings/categories/save', [SettingsController::class, 'categoriesSave'])->name('settings.categories.save');
    Route::post('/settings/categories/delete', [SettingsController::class, 'categoriesDelete'])->name('settings.categories.delete');
});