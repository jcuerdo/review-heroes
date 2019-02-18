FROM php:7.1.0-fpm
# Install modules
RUN apt-get update \
    && apt-get install -y \
	cron \
	libmcrypt-dev  \
	libicu-dev \
	mysql-client \
	php-pear \
    && pecl install redis \
    && docker-php-ext-enable redis \
	&& docker-php-ext-install pdo_mysql \
	&& docker-php-ext-install iconv \
	&& docker-php-ext-install mcrypt \
	&& docker-php-ext-install intl \
	&& docker-php-ext-install opcache \
	&& docker-php-ext-install mbstring \
	&& apt-get install redis-tools -y
CMD ["php-fpm"]

