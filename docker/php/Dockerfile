# ###############################
# ### Base official PHP image ###
# ###############################
FROM php:7.2-cli-stretch as poker-faces-php-base
LABEL maintainer="JPV"

ENV SHELL=/bin/bash

RUN set -xe \
    && mkdir -p /opt/code/poker-faces \
    # PHP extensions installations
    #   Xml
    #   Zip/Unzip
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive DEBCONF_NONINTERACTIVE_SEEN=true apt-get install -y --no-install-recommends \
        libxml2-dev \
        zip unzip libzip-dev \
    && docker-php-ext-configure xml \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install \
        xml \
        zip \
    && rm -rf /var/lib/apt/lists/* \
    && touch /var/log/php_errors.log

WORKDIR /opt/code/poker-faces

CMD ["tail", "-f", "/var/log/php_errors.log"]

# #####################
# ### Builder image ###
# #####################
FROM poker-faces-php-base as poker-faces-php-builder

# Composer installation
RUN set -xe \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && echo "" > /etc/profile.d/composer-memory-limit.sh \
    && echo "export COMPOSER_MEMORY_LIMIT=-1" >> /etc/profile.d/composer-memory-limit.sh

# #######################
# ### Developer image ###
# #######################
FROM poker-faces-php-builder as poker-faces-php-dev

# PHP configuration
RUN set -xe \
    && cp -p /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    # memory_limit
    && sed -r -i 's/^(\s*)memory_limit\s*=\s*[0-9]+[kKmMgG]*(.*)?$/\1memory_limit = 2G\2/g' /usr/local/etc/php/php.ini \
    # max_execution_time
    && sed -r -i 's/^(\s*)max_execution_time\s*=\s*[0-9]+(.*)?$/\1max_execution_time = 60\2/g' /usr/local/etc/php/php.ini

# XDebug PECL extension installation
COPY ./docker/php/conf.d/xdebug-config.ini /tmp/xdebug-config.ini
RUN set -xe \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && cat /tmp/xdebug-config.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && rm -f /tmp/xdebug-config.ini

# Dev utils installation
RUN set -xe \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive DEBCONF_NONINTERACTIVE_SEEN=true apt-get install -y --no-install-recommends bash-completion less vim.tiny git \
    && rm -rf /var/lib/apt/lists/* \
    && echo "" >> /etc/bash.bashrc && echo "if ! shopt -oq posix; then\n  if [ -f /usr/share/bash-completion/bash_completion ]; then\n    . /usr/share/bash-completion/bash_completion\n  elif [ -f /etc/bash_completion ]; then\n    . /etc/bash_completion\n  fi\nfi" >> /etc/bash.bashrc \
    && echo "" >> /etc/bash.bashrc \
    && echo "alias ls='ls --color=auto'" >> /etc/bash.bashrc \
    && echo "alias ll='ls --color=auto -alF'" >> /etc/bash.bashrc \
    && echo "alias grep='grep --color=auto'" >> /etc/bash.bashrc \
    && echo "alias fgrep='fgrep --color=auto'" >> /etc/bash.bashrc \
    && echo "alias egrep='egrep --color=auto'" >> /etc/bash.bashrc

ENV COMPOSER_MEMORY_LIMIT=-1 \
    APP_ENV=dev \
    APP_DEBUG=1 \
    APP_SECRET=secret-from-docker-file

# Symfony console autocomplete installation
RUN set -xe \
    && composer global require bamarni/symfony-console-autocomplete \
    && echo "" >> ~/.bashrc && echo "export PATH=${PATH}:$(composer global config --absolute bin-dir)" >> ~/.bashrc \
    && echo "" >> ~/.bashrc && echo 'eval "$(symfony-autocomplete)"' >> ~/.bashrc

# ####################################
# ### Deploy image - Create source ###
# ####################################
FROM poker-faces-php-builder as poker-faces-php-deploy-source

ENV COMPOSER_MEMORY_LIMIT=-1 \
    APP_ENV=prod \
    APP_DEBUG=0 \
    APP_SECRET=secret-from-docker-file

COPY . .

RUN set -xe \
    && rm -rf \
        ./bin/phpunit \
        ./config/packages/dev \
        ./config/packages/test \
        ./config/routes/dev \
        ./data \
        ./docker \
        ./public/bundles \
        ./tests \
        ./var \
        ./vendor \
        .env* \
        .git \
        .gitignore \
        .gitlab-ci.yml \
        .php_cs.dist \
        codeception.yml \
        phpcs.xml.dist \
        phpunit.xml.dist \
    && composer install --no-dev \
    && touch poker-faces-php-deploy.tar.gz \
    && tar cfz poker-faces-php-deploy.tar.gz \
        --exclude poker-faces-php-deploy.tar.gz \
        --exclude ./bin/phpunit \
        --exclude ./config/packages/dev \
        --exclude ./config/packages/test \
        --exclude ./config/routes/dev \
        --exclude ./data \
        --exclude ./docker \
        --exclude ./tests \
        --exclude .env* \
        --exclude .git \
        --exclude .gitignore \
        --exclude .gitlab-ci.yml \
        --exclude .php_cs.dist \
        --exclude codeception.yml \
        --exclude composer.json \
        --exclude composer.lock \
        --exclude phpcs.xml.dist \
        --exclude phpunit.xml.dist \
        --exclude symfony.lock \
        .

# ####################
# ### Deploy image ###
# ####################
FROM poker-faces-php-base as poker-faces-php-deploy

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    APP_SECRET=secret-from-docker-file

COPY --from=poker-faces-php-deploy-source /var/www/adsbooking-backend/poker-faces-php-deploy.tar.gz .

RUN set -xe \
    && tar xfz poker-faces-php-deploy.tar.gz \
    && rm -f poker-faces-php-deploy.tar.gz
