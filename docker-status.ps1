<#
.SYNOPSIS
    التحقق من حالة وسجلات حاوية المكتبة الإلكترونية
#>

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "     حالة حاوية المكتبة الإلكترونية (elibrary_app)" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

& docker ps --filter name="^/elibrary_app$"

Write-Host "`n[+] آخر 20 سطر من سجلات الحاوية (Logs):" -ForegroundColor Yellow
Write-Host "----------------------------------------------------------" -ForegroundColor DarkGray
& docker logs --tail 20 elibrary_app
Write-Host "----------------------------------------------------------" -ForegroundColor DarkGray
