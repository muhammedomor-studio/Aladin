FROM php:8.3-cli
WORKDIR /var/www/html
COPY . .
ENV PORT=10000
EXPOSE 10000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} index.php"]
