FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libgd-dev \
    libxml2-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql mysqli zip exif opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure Apache
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod expires

# Set PHP configurations
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/docker-php-memory-limit.ini
RUN echo "upload_max_filesize = 40M" > /usr/local/etc/php/conf.d/docker-php-upload-size.ini
RUN echo "post_max_size = 40M" >> /usr/local/etc/php/conf.d/docker-php-upload-size.ini

# Create upload directories
RUN mkdir -p /var/www/html/uploads/media /var/www/html/uploads/profile_pics

# Copy Apache configuration and PHP settings
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini

# Copy application files
COPY --chown=www-data:www-data . /var/www/html/

# Remove any Windows-specific files that could cause issues
RUN find /var/www/html -name "*DPrograms*" -delete || true
RUN rm -f /var/www/html/php.ini

# Ensure upload directories have proper permissions
RUN chown -R www-data:www-data /var/www/html/uploads

# Run composer install if composer.json exists
RUN if [ -f "composer.json" ]; then composer install --no-dev; fi

# Set the proper port from environment variable
CMD sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && apache2-foreground 