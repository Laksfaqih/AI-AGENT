# Indonesia AI Agent - Docker Configuration
# Multi-stage build for optimized production image

# Stage 1: Build stage
FROM php:8.2-cli AS builder

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Stage 2: Production stage
FROM php:8.2-cli

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    sqlite3 \
    && docker-php-ext-install zip pdo pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Create app user
RUN useradd -m -u 1000 appuser

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=appuser:appuser . .

# Copy vendor from builder stage
COPY --from=builder --chown=appuser:appuser /app/vendor ./vendor

# Create storage directory and set permissions
RUN mkdir -p storage && \
    chown -R appuser:appuser storage && \
    chmod -R 775 storage

# Switch to app user
USER appuser

# Expose port
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -r "echo 'OK';" || exit 1

# Run migrations and start server
CMD php migrate.php && php -S 0.0.0.0:8080 -t public
