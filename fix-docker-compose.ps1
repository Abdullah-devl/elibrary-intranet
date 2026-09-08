<#
.SYNOPSIS
    إصلاح مشكلة Unsupported 16-Bit Application في Docker Compose على Windows Server 2016
.DESCRIPTION
    يقوم السكربت بتفعيل بروتوكول TLS 1.2 وتحميل النسخة الرسمية الصالحة 64-bit من Docker Compose (v1.29.2)
    المتوافقة تماماً مع Windows Server 2016 ومحرك Docker Enterprise 17.06.2.
#>

$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "  أداة إصلاح وتثبيت Docker Compose 64-bit لـ Windows Server 2016" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$dockerDir = "C:\Program Files\docker"
$targetExe = Join-Path $dockerDir "docker-compose.exe"
$tempExe   = Join-Path $env:TEMP "docker-compose-x64.exe"

# 1. التأكد من وجود مجلد Docker
if (-not (Test-Path $dockerDir)) {
    Write-Host "[+] جاري إنشاء مجلد Docker: $dockerDir" -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $dockerDir -Force | Out-Null
}

# 2. تفعيل TLS 1.2 الإجباري للاتصال بـ GitHub
Write-Host "[1/4] تفعيل بروتوكول الأمان TLS 1.2..." -ForegroundColor Yellow
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls

# 3. رابط النسخة الرسمية المتوافقة 64-bit v1.29.2
# هذه النسخة تعمل مع Docker Enterprise 17.06.2 وتدعم schema version 2.2
$downloadUrl = "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-Windows-x86_64.exe"

Write-Host "[2/4] جاري تحميل docker-compose.exe (64-bit)... يرجى الانتظار" -ForegroundColor Yellow
Write-Host "      المصدر: $downloadUrl" -ForegroundColor DarkGray

try {
    # استخدام WebClient لضمان تنزيل الملف الثنائي بالكامل ومتابعة الـ Redirects تلقائياً
    $webClient = New-Object System.Net.WebClient
    $webClient.Headers.Add("User-Agent", "PowerShell/WindowsServer2016")
    $webClient.DownloadFile($downloadUrl, $tempExe)
}
catch {
    Write-Host "[-] فشل التحميل التلقائي عبر WebClient! محاولة باستخدام Invoke-WebRequest..." -ForegroundColor Yellow
    Invoke-WebRequest -Uri $downloadUrl -OutFile $tempExe -UseBasicParsing
}

# 4. التحقق من حجم الملف
if (-not (Test-Path $tempExe)) {
    Write-Host "[-] خطأ: لم يتم العثور على الملف المحمل!" -ForegroundColor Red
    exit 1
}

$fileSize = (Get-Item $tempExe).Length
$fileSizeMB = [math]::Round($fileSize / 1MB, 2)

Write-Host "[3/4] حجم الملف المحمل: $fileSizeMB ميجابايت" -ForegroundColor Cyan

# إذا كان حجم الملف أقل من 5 ميجابايت فهذا يعني أنه صفحة خطأ HTML وليس البرنامج الثنائي الحقيقي
if ($fileSize -lt (5 * 1024 * 1024)) {
    Write-Host "[-] خطأ: الملف المحمل تالف أو صفحة خطأ HTML (حجمه صغير جداً: $fileSizeMB MB)!" -ForegroundColor Red
    Write-Host "[-] سبب ظهور خطأ 'Unsupported 16-Bit' هو حفظ ملف HTML كـ exe." -ForegroundColor Red
    Remove-Item $tempExe -Force -ErrorAction SilentlyContinue
    exit 1
}

# 5. استبدال الملف في مسار Docker
Write-Host "[4/4] تثبيت الملف في: $targetExe" -ForegroundColor Yellow
if (Test-Path $targetExe) {
    Remove-Item $targetExe -Force -ErrorAction SilentlyContinue
}
Move-Item -Path $tempExe -Destination $targetExe -Force

# 6. إضافة مسار Docker إلى متغيرات النظام PATH إن لم يكن موجوداً
$machinePath = [Environment]::GetEnvironmentVariable("Path", "Machine")
if ($machinePath -notlike "*$dockerDir*") {
    Write-Host "[+] إضافة مسار Docker إلى متغيرات النظام PATH..." -ForegroundColor Yellow
    [Environment]::SetEnvironmentVariable("Path", "$machinePath;$dockerDir", "Machine")
    $env:Path = "$env:Path;$dockerDir"
}

Write-Host "`n[✓] تم التثبيت بنجاح! فحص النسخة الآن:" -ForegroundColor Green
try {
    & "$targetExe" version
}
catch {
    Write-Host "[-] تعذر تشغيل الأمر مباشرة: $_" -ForegroundColor Yellow
}

Write-Host "`nيمكنك الآن تشغيل 'docker-compose up -d --build' بدون أخطاء!" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Cyan
