# دليل التثبيت والتشغيل على خادم Windows Server 2016 خطوة بخطوة

تم إعداد هذا الدليل خصيصاً لبيئة **Windows Server 2016 (64-bit)** مع محرك **Docker Enterprise 17.06.2-ee-16**، مع الحفاظ الكامل على جميع وظائف وخصائص نظام المكتبة الإلكترونية.

---

## تشخيص سبب المشاكل السابقة
1. **خطأ `Unsupported 16-Bit Application` عند تشغيل `docker-compose.exe`:**
   - نظام Windows 64-bit يطلق هذه الرسالة عند محاولة تشغيل ملف تنفيذي تالف أو غير صالح (مثل تنزيل صفحة خطأ HTML من GitHub بدلاً من الملف الثنائي، أو انقطاع التنزيل، أو عدم تفعيل بروتوكول TLS 1.2 في PowerShell عند التحميل).
   - تم إنشاء سكربت `fix-docker-compose.ps1` لتحميل النسخة الرسمية الحقيقية 64-bit (حجمها قرابة 50 ميجابايت).
2. **عدم توافق `docker-compose.yml` السابق:**
   - تم تحديث الملف ليتضمن `version: '2.2'` ليكون متوافقاً 100% مع Docker 17.06.2 ومواصفات Docker API 1.30.
3. **تحديث الـ `Dockerfile`:**
   - تم استبدال `COPY --from=composer` بتثبيت مباشر متوافق مع كافة الإصدارات القديمة دون أخطاء في الـ Cache أو السحب المتعدد.

---

## الخيار الأول: التشغيل عبر Docker المباشر بدون Compose (الأسهل والأضمن) ⭐

بما أن المشروع يعتمد على حاوية واحدة فقط، يمكنك الاستغناء تماماً عن `docker-compose` واستخدام أوامر Docker الأصلية المدعومة 100% في نسختك.

### الخطوات:
1. افتح موجه أوامر **PowerShell كمسؤول (Run as Administrator)**.
2. انتقل إلى مجلد المشروع على السيرفر:
   ```powershell
   cd "C:\Users\Al_Hamed\Desktop\elibrary"
   ```
3. قم بتشغيل السكربت المخصص:
   ```powershell
   .\docker-start.ps1
   ```
   **ماذا سيفعل السكربت تلقائياً؟**
   - سيتأكد من اتصال محرك Docker وتشغيل الخدمة.
   - سيبني صورة المشروع `elibrary-app`.
   - سينشئ مجلدات الـ Volumes الدائمة (`elibrary_storage` و `elibrary_database`) لضمان عدم ضياع أي ملفات أو بيانات.
   - سيشغل الحاوية على المنفذ `80` مع خاصية الإقلاع التلقائي `restart: always`.
   - سيطبق أوامر Laravel الأساسية (`key:generate`, `migrate`, الكاش الإنتاجي).

4. **للإيقاف أو المراقبة:**
   - لفحص حالة الحاوية وسجلاتها: `.\docker-status.ps1`
   - لإيقاف الحاوية بأمان: `.\docker-stop.ps1`

---

## الخيار الثاني: إصلاح وتشغيل Docker Compose

إذا كنت ترغب بالاستمرار في استخدام `docker-compose`:

### الخطوات:
1. افتح موجه أوامر **PowerShell كمسؤول (Run as Administrator)**.
2. نفذ سكربت الإصلاح المرفق بالمشروع:
   ```powershell
   cd "C:\Users\Al_Hamed\Desktop\elibrary"
   .\fix-docker-compose.ps1
   ```
   *يقوم السكربت بتفعيل بروتوكول TLS 1.2 وتنزيل النسخة الرسمية 64-bit v1.29.2 بحجم ~50MB واستبدال الملف التالف في `C:\Program Files\docker\docker-compose.exe`.*
3. تأكد من أن الأمر يعمل الآن بدون أخطاء عبر كتابة:
   ```powershell
   docker-compose version
   ```
4. شغل المشروع بالطريقة المعتادة:
   ```powershell
   docker-compose up -d --build
   ```

---

## الخيار الثالث: التشغيل كخدمة ويندوز أصلية عبر NSSM (بديل فائق السرعة والاستقرار) 🚀

> **تنبيه مهم حول Windows Server 2016:**
> محرك Docker Enterprise 17.06 على Windows Server 2016 يعمل بوضع **Windows Containers** بشكل افتراضي. إذا ظهرت رسالة:
> `image operating system "linux" cannot be used on this platform`
> فهذا يعني أن نظام تشغيل السيرفر لديك غير مهيأ لتشغيل حاويات لينكس.
> في هذه الحالة، الخيار الأفضل والأنسب هو تشغيل Laravel مباشرة كـ **Windows Service** وهو الحل الأكثر كفاءة وخفة وسرعة لنظام Windows Server 2016.

### الخطوات:
1. تأكد من توفر PHP 8.2 على السيرفر (أو قم بتنزيله واستخراجه في مجلد مثل `C:\php`).
2. افتح PowerShell كمسؤول ونفذ:
   ```powershell
   cd "C:\Users\Al_Hamed\Desktop\elibrary"
   .\install-windows-service.ps1
   ```
   *(إذا كان مسار PHP مخصصاً يمكنك تمريره هكذا: `.\install-windows-service.ps1 -PhpPath "C:\php\php.exe"`)*
3. بعد التثبيت:
   - ستعمل الخدمة باسم `ELibraryService` تلقائياً في الخلفية ومع كل إعادة تشغيل للسيرفر.
   - يمكنك التحكم بها بالزر الأخضر والأحمر عبر تشغيل التطبيق المرفق: **`ELibraryAdmin.exe`**.

---

## ضبط جدار الحماية (Firewall) للسماح بالدخول عبر الشبكة

لكي يتمكن المستخدمون في الشبكة المحلية من تصفح المكتبة، افتح المنفذ 80 في جدار الحماية بتنفيذ هذا الأمر في PowerShell كمسؤول:

```powershell
New-NetFirewallRule -DisplayName "ELibrary Web Port 80" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow
```

ثم يمكنهم الدخول مباشرة بكتابة عنوان الـ IP الخاص بالسيرفر في المتصفح، مثلاً:
`http://192.168.1.100`
