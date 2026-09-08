<#
.SYNOPSIS
    إيقاف وإزالة حاوية مشروع المكتبة الإلكترونية بأمان
#>

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "       إيقاف سيرفر المكتبة الإلكترونية (Docker)" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$existing = & docker ps -a -q --filter name="^/elibrary_app$"
if ($existing) {
    Write-Host "[+] جاري إيقاف الحاوية elibrary_app..." -ForegroundColor Yellow
    & docker stop elibrary_app
    Write-Host "[+] جاري إزالة الحاوية..." -ForegroundColor Yellow
    & docker rm elibrary_app
    Write-Host "[✓] تم إيقاف وحذف الحاوية بأمان! ملاحظة: تم الحفاظ على قواعد البيانات والتخزين داخل الـ Volumes." -ForegroundColor Green
} else {
    Write-Host "[i] الحاوية elibrary_app غير مشغلة حالياً." -ForegroundColor Yellow
}
