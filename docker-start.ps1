<#
.SYNOPSIS
    تشغيل وتحديث مشروع Laravel في بيئة Docker Enterprise 17.06.2 مباشرة وبدون الحاجة لـ docker-compose
.DESCRIPTION
    يستخدم هذا السكربت أوامر Docker الأصلية المدعومة 100% في Docker 17.06.2-ee.
    يقوم ببناء الصورة، إنشاء الـ Volumes المستدامة، وتشغيل الحاوية على المنفذ 80.
#>

$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "    تشغيل منصة المكتبة الإلكترونية (Docker Enterprise 17.06)" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. التأكد من مسار Docker
$dockerExe = "docker"
if (-not (Get-Command "docker" -ErrorAction SilentlyContinue)) {
    if (Test-Path "C:\Program Files\docker\docker.exe") {
        $env:Path = "$env:Path;C:\Program Files\docker"
    } else {
        Write-Host "[-] لم يتم العثور على docker.exe! تأكد من تثبيته في C:\Program Files\docker" -ForegroundColor Red
        exit 1
    }
}

# 2. التأكد من أن خدمة Docker تعمل
Write-Host "[1/6] التحقق من اتصال محرك Docker Engine..." -ForegroundColor Yellow
try {
    $dockerInfo = & docker info 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[-] محرك Docker غير متصل أو الخدمة متوقفة! جاري محاولة تشغيل خدمة Docker..." -ForegroundColor Yellow
        Start-Service -Name "docker" -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 3
    }
} catch {
    Write-Host "[-] تعذر الاتصال بمحرك Docker! تأكد من تشغيل خدمة docker عبر: Start-Service docker" -ForegroundColor Red
    exit 1
}

# 3. التأكد من وجود ملف .env
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Write-Host "[+] إنشاء ملف .env من .env.example..." -ForegroundColor Yellow
        Copy-Item ".env.example" ".env"
    }
}

# 4. بناء صورة Docker الخاصة بالمشروع
Write-Host "`n[2/6] بناء صورة التطبيق (Building Docker Image: elibrary-app)..." -ForegroundColor Yellow
& docker build -t elibrary-app -f Dockerfile .
if ($LASTEXITCODE -ne 0) {
    Write-Host "[-] فشل بناء صورة Docker!" -ForegroundColor Red
    exit 1
}

# 5. إنشاء الـ Volumes للحفاظ على قاعدة البيانات والملفات المرفوعة
Write-Host "`n[3/6] التحقق من مجلدات التخزين المستدامة (Volumes)..." -ForegroundColor Yellow
$volumes = @("elibrary_storage", "elibrary_database")
foreach ($vol in $volumes) {
    $check = & docker volume ls -q --filter name="^$vol$"
    if (-not $check) {
        Write-Host "[+] إنشاء Volume: $vol" -ForegroundColor Cyan
        & docker volume create "$vol" | Out-Null
    }
}

# 6. إيقاف وإزالة أي حاوية سابقة إن وجدت
Write-Host "`n[4/6] تنظيف الحاوية السابقة (إن وجدت)..." -ForegroundColor Yellow
$existing = & docker ps -a -q --filter name="^/elibrary_app$"
if ($existing) {
    Write-Host "[+] إيقاف الحاوية الحالية..." -ForegroundColor DarkGray
    & docker stop elibrary_app 2>&1 | Out-Null
    Write-Host "[+] حذف الحاوية القديمة استعداداً للتشغيل الجديد..." -ForegroundColor DarkGray
    & docker rm elibrary_app 2>&1 | Out-Null
}

# 7. تشغيل الحاوية الجديدة
Write-Host "`n[5/6] بدء تشغيل حاوية التطبيق (elibrary_app)..." -ForegroundColor Yellow
& docker run -d `
    --name elibrary_app `
    -p 80:80 `
    --restart always `
    -v "elibrary_storage:/var/www/html/storage" `
    -v "elibrary_database:/var/www/html/database" `
    --env-file ".env" `
    -e APP_ENV=production `
    -e APP_DEBUG=false `
    elibrary-app

if ($LASTEXITCODE -ne 0) {
    Write-Host "[-] حدث خطأ أثناء تشغيل الحاوية!" -ForegroundColor Red
    exit 1
}

# 8. تنفيذ أوامر التهيئة داخل الحاوية (Migrations & Optimization)
Write-Host "`n[6/6] تطبيق التحديثات وإعداد قاعدة البيانات داخل الحاوية..." -ForegroundColor Yellow
Start-Sleep -Seconds 3

# توليد المفتاح إن لم يكن موجوداً
& docker exec elibrary_app php artisan key:generate --force
# ترحيل قاعدة البيانات SQLite
& docker exec elibrary_app php artisan migrate --force
# تحسين الكاش لبيئة الإنتاج
& docker exec elibrary_app php artisan config:cache
& docker exec elibrary_app php artisan route:cache
& docker exec elibrary_app php artisan view:cache

Write-Host "`n==========================================================" -ForegroundColor Green
Write-Host "  [✓] تم تشغيل السيرفر بنجاح وهو يعمل الآن على المنفذ 80!" -ForegroundColor Green
Write-Host "  يمكنك تصفح الموقع عبر المتصفح من خلال:" -ForegroundColor Cyan
Write-Host "  - محلياً:   http://localhost" -ForegroundColor White
Write-Host "  - في الشبكة: http://<SERVER_IP>" -ForegroundColor White
Write-Host "==========================================================" -ForegroundColor Green
