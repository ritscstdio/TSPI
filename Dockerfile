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
    && docker-php-ext-install pdo_mysql mysqli zip exif opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure Apache
RUN a2enmod rewrite
RUN a2enmod headers

# Set PHP configurations
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/docker-php-memory-limit.ini
RUN echo "upload_max_filesize = 40M" > /usr/local/etc/php/conf.d/docker-php-upload-size.ini
RUN echo "post_max_size = 40M" >> /usr/local/etc/php/conf.d/docker-php-upload-size.ini

# Copy application files 
# Exclude files that might cause conflicts
COPY --chown=www-data:www-data . /var/www/html/
RUN rm -f /var/www/html/php.ini
RUN rm -f /var/www/html/DProgramsXAMPPphplogsphp_error_log

# Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Copy custom php.ini settings (after removing Windows-specific configuration)
COPY docker/custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini

# Make sure the uploads directory is writable
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads

# Run composer install if composer.json exists
RUN if [ -f "composer.json" ]; then composer install --no-dev; fi

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 