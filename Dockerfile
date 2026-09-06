FROM php:8.2-apache

# تثبيت الحزم الأساسية للنظام
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nodejs \
    npm

# تنظيف الكاش الخاص بـ apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# تثبيت إضافات PHP المطلوبة لـ Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo_sqlite

# تفعيل mod_rewrite الخاص بـ Apache ليعمل توجيه Laravel بشكل صحيح
RUN a2enmod rewrite

# تغيير المجلد الافتراضي لـ Apache ليكون مجلد public الخاص بـ Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تحديد مسار العمل داخل الحاوية
WORKDIR /var/www/html

# نسخ ملفات المشروع إلى الحاوية
COPY . .

# تثبيت مكتبات PHP
RUN composer install --no-interaction --optimize-autoloader

# تثبيت مكتبات Node.js وبناء الواجهات (Vite/Tailwind)
RUN npm install
RUN npm run build

# إعداد الصلاحيات لمجلدات التخزين
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# إعداد ملف قاعدة بيانات SQLite وصلاحياته
RUN touch /var/www/html/database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/database \
    && chmod -R 777 /var/www/html/database \
    && chmod 666 /var/www/html/database/database.sqlite

# فتح المنفذ 80
EXPOSE 80
