FROM php:7.4-cli

MAINTAINER eResults

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && chmod a+x /usr/local/bin/composer

# Install php extensions
RUN apt-get update \
	&& apt-get install -y --no-install-recommends libzip-dev

# PHP EXTENSIONS
RUN docker-php-ext-install -j$(nproc) zip sysvmsg sysvshm \
    && docker-php-source delete
