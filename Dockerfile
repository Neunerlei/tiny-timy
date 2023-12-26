FROM index.docker.io/library/php:7.4-cli
ARG DOCKER_RUNTIME
ARG DOCKER_GID
ARG DOCKER_UID
ENV DOCKER_RUNTIME=${DOCKER_RUNTIME:-docker}

# We need this as compatiblity for podman users
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
	libxml2-dev \
	git \
	libzip-dev \
	zip \
	unzip

RUN docker-php-ext-install \
	zip \
	intl

RUN pecl install xdebug-3.0.4 && docker-php-ext-enable xdebug

RUN apt-get update && apt-get install -y libmemcached-dev
RUN pecl install memcached && docker-php-ext-enable memcached

COPY --from=index.docker.io/library/composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd -g ${DOCKER_GID} test_user
RUN if [ "${DOCKER_RUNTIME}" = "docker" ]; then adduser -u ${DOCKER_UID} --gid ${DOCKER_GID} test_user; fi

RUN mkdir -p /opt/project
RUN mkdir -p /opt/composer
RUN if [ "${DOCKER_RUNTIME}" = "docker" ] ; then chown ${DOCKER_UID}:${DOCKER_GID} /opt/composer ; fi

WORKDIR /opt/project
