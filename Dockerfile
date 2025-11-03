FROM php:8.2-cli

# Install mysqli and pdo extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files
COPY . /app
WORKDIR /app

# Expose port
EXPOSE 8080

# Start PHP built-in server
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t ."]