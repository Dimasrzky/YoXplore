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
    dnsutils && \
    rm -rf /var/lib/apt/lists/*  # Clean up unnecessary files to reduce image size


# Install PHP extensions for GD, PDO, and MySQL support             
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql mysqli gd

# Enable Apache modules
RUN a2enmod rewrite env

# Set working directory for the application
WORKDIR /var/www/html

# Copy custom Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf

# Copy application files into the container
COPY . /var/www/html/

# Set proper permissions for the web files
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Copy dnsmasq configuration file (assuming you have this file ready)
COPY dnsmasq.conf /etc/dnsmasq.conf

# Copy custom entrypoint script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Set entrypoint to start dnsmasq and Apache
ENTRYPOINT ["/usr/local/bin/start.sh"]

# Expose necessary ports (HTTP and DNS)
EXPOSE 80 53/udp

