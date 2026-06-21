<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $settingsPath = storage_path('app/settings.json');
        $defaultSettings = [
            'name' => 'E-Library | المكتبة الإلكترونية',
            'logo_type' => 'icon', // 'icon' or 'image'
            'logo_icon' => 'fa-solid fa-graduation-cap',
            'logo_path' => '',
            'color_primary' => '#1E293B',
            'color_accent' => '#D97706',
            'color_bglight' => '#F8FAFC',
            'categories' => [
                [
                    'id' => 'videos',
                    'name' => 'المحاضرات المرئية',
                    'icon' => 'fa-solid fa-film',
                    'path' => str_replace('\\', '/', public_path('videos')),
                    'extensions' => ['mp4', 'webm', 'mkv', 'avi'],
                    'layout' => 'video'
                ],
                [
                    'id' => 'books',
                    'name' => 'الكتب والمراجع',
                    'icon' => 'fa-solid fa-book-open',
                    'path' => str_replace('\\', '/', public_path('books')),
                    'extensions' => ['pdf', 'epub', 'docx', 'doc', 'xlsx', 'xls', 'pptx', 'ppt', 'txt'],
                    'layout' => 'document'
                ],
                [
                    'id' => 'programs',
                    'name' => 'البرامج والأدوات',
                    'icon' => 'fa-solid fa-laptop-code',
                    'path' => str_replace('\\', '/', public_path('programs')),
                    'extensions' => ['exe', 'zip', 'rar', '7z', 'msi'],
                    'layout' => 'download'
                ]
            ],
            'admin_email' => 'admin@gmail.com',
            'admin_password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            'welcome_text' => 'منصة ذكية ومتكاملة مصممة خصيصاً لتسهيل تصفح وعرض المحاضرات التعليمية، الكتب والمراجع الدراسية، بالإضافة إلى البرامج والأدوات التقنية الهامة في مكان واحد وبسرعة فائقة داخل الشبكة المحلية.'
        ];

        if (\Illuminate\Support\Facades\File::exists($settingsPath)) {
            $settings = json_decode(\Illuminate\Support\Facades\File::get($settingsPath), true) ?: [];
            
            // هجرة الإعدادات القديمة إلى بنية الأقسام الجديدة إن لم تكن موجودة
            if (!isset($settings['categories'])) {
                $settings['categories'] = [
                    [
                        'id' => 'videos',
                        'name' => 'المحاضرات المرئية',
                        'icon' => 'fa-solid fa-film',
                        'path' => str_replace('\\', '/', $settings['path_videos'] ?? public_path('videos')),
                        'extensions' => ['mp4', 'webm', 'mkv', 'avi'],
                        'layout' => 'video'
                    ],
                    [
                        'id' => 'books',
                        'name' => 'الكتب والمراجع',
                        'icon' => 'fa-solid fa-book-open',
                        'path' => str_replace('\\', '/', $settings['path_books'] ?? public_path('books')),
                        'extensions' => ['pdf', 'epub', 'docx', 'doc', 'xlsx', 'xls', 'pptx', 'ppt', 'txt'],
                        'layout' => 'document'
                    ],
                    [
                        'id' => 'programs',
                        'name' => 'البرامج والأدوات',
                        'icon' => 'fa-solid fa-laptop-code',
                        'path' => str_replace('\\', '/', $settings['path_programs'] ?? public_path('programs')),
                        'extensions' => ['exe', 'zip', 'rar', '7z', 'msi'],
                        'layout' => 'download'
                    ]
                ];
            }
            
            $settings = array_merge($defaultSettings, $settings);
        } else {
            $settings = $defaultSettings;
            \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($settingsPath));
            \Illuminate\Support\Facades\File::put($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        \Illuminate\Support\Facades\View::share('settings', (object) $settings);
    }
}
