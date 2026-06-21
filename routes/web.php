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
Route::post('/library/copy-to-drive', [LibraryController::class, 'copyToDrive'])->name('library.copy_to_drive');

// مسارات صفحة الإعدادات والحماية
Route::get('/settings/login', [SettingsController::class, 'showLogin'])->name('settings.login');
Route::post('/settings/login', [SettingsController::class, 'login'])->name('settings.login_submit');
Route::post('/settings/logout', [SettingsController::class, 'logout'])->name('settings.logout');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

// مسارات إدارة الأقسام ديناميكياً
Route::get('/settings/categories', [SettingsController::class, 'categoriesIndex'])->name('settings.categories.index');
Route::post('/settings/categories/save', [SettingsController::class, 'categoriesSave'])->name('settings.categories.save');
Route::post('/settings/categories/delete', [SettingsController::class, 'categoriesDelete'])->name('settings.categories.delete');