# Stage 1: Build assets using Node
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Build the production image
FROM richarvey/nginx-php-fpm:3.1.6

WORKDIR /var/www/html

# Copy all application files
COPY . .

# Copy custom Nginx configuration
COPY conf/nginx/nginx-site.conf /etc/nginx/sites-available/default.conf

# Copy built assets from builder stage
COPY --from=assets-builder /app/public/build ./public/build

# Run composer install during the build phase
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Set permissions for storage and bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Ensure the deploy script is executable
RUN chmod +x /var/www/html/scripts/00-laravel-deploy.sh

CMD ["/start.sh"]
