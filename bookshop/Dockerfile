# Start from an official image that already has PHP 8.2 + Apache installed
FROM php:8.2-apache

# Install the PDO MySQL extension — PHP doesn't talk to MySQL out of the box,
# this driver is what config/db.php's `new PDO(...)` call needs to work.
RUN docker-php-ext-install pdo pdo_mysql

# Apache's default folder it serves files from
WORKDIR /var/www/html

# Copy everything from this project folder into the container
COPY . /var/www/html/

# Let Apache rewrite URLs if needed later (not required now, but harmless/common)
RUN a2enmod rewrite

# Make sure the uploads folder is writable by the web server user (www-data),
# same reason we set permissions on InfinityFree
RUN chown -R www-data:www-data /var/www/html/uploads
