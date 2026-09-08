<#
.SYNOPSIS
    تثبيت وتشغيل مشروع Laravel كخدمة ويندوز رسمية (Windows Service) عبر أداة NSSM
.DESCRIPTION
    هذا الخيار فائق الاستقرار وسريع جداً وخالٍ من مشاكل الحاويات، ويعمل على Windows Server 2016 مباشرة
    ومتكامل تماماً مع برنامج الإدارة الموجود بالمشروع (ELibraryAdmin.exe).
#>

param(
    [string]$PhpPath = "php.exe",
    [int]$Port = 80
)

$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "    تثبيت خدمة الويندوز الرسمية (ELibraryService) عبر NSSM" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$serviceName = "ELibraryService"
$projectDir = $PSScriptRoot
$nssmExe = Join-Path $projectDir "nssm.exe"

# 1. التأكد من وجود nssm.exe
if (-not (Test-Path $nssmExe)) {
    Write-Host "[+] جاري تحميل أداة nssm.exe تلقائياً لبيئة Windows Server 64-bit..." -ForegroundColor Yellow
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    $zipPath = Join-Path $env:TEMP "nssm.zip"
    Invoke-WebRequest -Uri "https://nssm.cc/release/nssm-2.24.zip" -OutFile $zipPath -UseBasicParsing
    Expand-Archive -Path $zipPath -DestinationPath (Join-Path $env:TEMP "nssm_extracted") -Force
    Copy-Item -Path (Join-Path $env:TEMP "nssm_extracted\nssm-2.24\win64\nssm.exe") -Destination $nssmExe -Force
    Write-Host "[✓] تم تجهيز nssm.exe في مجلد المشروع." -ForegroundColor Green
}

# 2. التحقق من مسار PHP
$resolvedPhp = $PhpPath
if (-not (Test-Path $resolvedPhp)) {
    $command = Get-Command "php" -ErrorAction SilentlyContinue
    if ($command) {
        $resolvedPhp = $command.Source
    } else {
        Write-Host "[-] لم يتم العثور على مسار php.exe! يرجى تمرير المسار مثلاً:" -ForegroundColor Yellow
        Write-Host "    .\install-windows-service.ps1 -PhpPath 'C:\php\php.exe'" -ForegroundColor White
        exit 1
    }
}

Write-Host "[1/4] مسار PHP: $resolvedPhp" -ForegroundColor DarkGray
Write-Host "[2/4] مسار المشروع: $projectDir" -ForegroundColor DarkGray

# 3. إيقاف وإزالة الخدمة القديمة إن وجدت
$existingService = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
if ($existingService) {
    Write-Host "[+] إيقاف وإزالة الخدمة السابقة..." -ForegroundColor Yellow
    & $nssmExe stop $serviceName 2>&1 | Out-Null
    & $nssmExe remove $serviceName confirm 2>&1 | Out-Null
}

# 4. تثبيت الخدمة عبر NSSM
Write-Host "[3/4] إنشاء خدمة الويندوز $serviceName..." -ForegroundColor Yellow
$artisanPath = Join-Path $projectDir "artisan"
$arguments = "artisan serve --host=0.0.0.0 --port=$Port"

& $nssmExe install $serviceName $resolvedPhp $arguments
& $nssmExe set $serviceName AppDirectory $projectDir
& $nssmExe set $serviceName DisplayName "Electronic Library Laravel Server"
& $nssmExe set $serviceName Description "خادم تطبيق المكتبة الإلكترونية الداخلي"
& $nssmExe set $serviceName Start SERVICE_AUTO_START

# 5. تشغيل الخدمة
Write-Host "[4/4] بدء تشغيل الخدمة الآن..." -ForegroundColor Yellow
& $nssmExe start $serviceName

Start-Sleep -Seconds 2
$status = (Get-Service -Name $serviceName).Status
if ($status -eq "Running") {
    Write-Host "`n==========================================================" -ForegroundColor Green
    Write-Host "  [✓] الخدمة تعمل الآن بنجاح وبشكل تلقائي عند إقلاع السيرفر!" -ForegroundColor Green
    Write-Host "  يمكنك الآن أيضاً استخدام واجهة التحكم: ELibraryAdmin.exe" -ForegroundColor Cyan
    Write-Host "  تصفح الموقع عبر: http://localhost أو IP السيرفر." -ForegroundColor White
    Write-Host "==========================================================" -ForegroundColor Green
} else {
    Write-Host "[-] الخدمة في حالة: $status. يرجى التحقق من السجلات." -ForegroundColor Red
}
