FROM php:8.2-apache

# Install system utilities needed by Composer
RUN apt-get update && \
    apt-get install -y git zip unzip && \
    rm -rf /var/lib/apt/lists/*

# Copy Composer binary from official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files into container
COPY . .

# Download dependencies and generate vendor/ autoload folder automatically
RUN composer install --no-dev --optimize-autoloader

# Apache default port is 80
EXPOSE 80