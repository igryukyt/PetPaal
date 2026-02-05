FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libonig-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install local PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Expose the port
EXPOSE 8080

# Start PHP built-in server
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t ."]
