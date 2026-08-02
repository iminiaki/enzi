FROM wordpress:latest

RUN apt-get update \
	&& apt-get install -y --no-install-recommends libxml2-dev \
	&& docker-php-ext-install soap opcache \
	&& apt-get purge -y --auto-remove libxml2-dev \
	&& rm -rf /var/lib/apt/lists/*

COPY enzi-opcache.ini /usr/local/etc/php/conf.d/enzi-opcache.ini
COPY enzi-cache.conf /etc/apache2/conf-available/enzi-cache.conf
RUN a2enmod expires headers \
	&& a2enconf enzi-cache
