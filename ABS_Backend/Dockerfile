FROM php:8.4-fpm
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libxslt1-dev \
    libbz2-dev \
    libonig-dev \
    libsqlite3-dev \
    libmcrypt-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    pdo_sqlite \
    gd \
    zip \
    bcmath \
    exif \
    pcntl \
    intl \
    xsl \
    bz2 \
    calendar \
    gettext \
    shmop \
    sockets \
    sysvmsg \
    sysvsem \
    sysvshm \
    opcache

RUN pecl install mcrypt && docker-php-ext-enable mcrypt

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN echo "upload_max_filesize=50M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/uploads.ini
