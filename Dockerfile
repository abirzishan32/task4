FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    nano \
    nginx \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js and NPM
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs build-essential \
    && npm install -g npm

# # Configure PHP for production
# COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Install Symfony dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Install and build frontend assets
RUN npm install && \
    npm run build && \
    rm -rf node_modules

# Set permissions
RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data var public/build && \
    chmod -R 777 var public/build

# Copy configuration files
COPY docker/nginx/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]