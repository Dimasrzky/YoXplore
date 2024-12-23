# Use PHP 8.0 with Apache as base image
FROM php:8.0-apache

# Install system dependencies, dnsmasq, and DNS utilities
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    dnsmasq \
    dnsutils \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for GD, PDO, and MySQL support             
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql mysqli gd

# Install wait-for-it script
ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh /usr/local/bin/wait-for-it
RUN chmod +x /usr/local/bin/wait-for-it

# Enable Apache modules
RUN a2enmod rewrite headers env

# Set working directory
WORKDIR /var/www/html

# Copy custom Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf

# Copy dnsmasq configuration and startup script
COPY dnsmasq.conf /etc/dnsmasq.conf
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/Image

# Expose ports
EXPOSE 80 53/udp

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/start.sh"]