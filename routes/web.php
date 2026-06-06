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

// مسارات صفحة الإعدادات والحماية
Route::get('/settings/login', [SettingsController::class, 'showLogin'])->name('settings.login');
Route::post('/settings/login', [SettingsController::class, 'login'])->name('settings.login_submit');
Route::post('/settings/logout', [SettingsController::class, 'logout'])->name('settings.logout');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');