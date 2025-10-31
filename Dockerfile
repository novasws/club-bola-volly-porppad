FROM php:8.2-cli

# Install extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring

# Copy files
COPY . /app
WORKDIR /app

# Start server
CMD php -S 0.0.0.0:${PORT:-8080} -t .