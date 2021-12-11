FROM webdevops/php-nginx:7.4-alpine


# Install Laravel framework system requirements
RUN apk add oniguruma-dev postgresql-dev libxml2-dev
RUN docker-php-ext-install \
        bcmath \
        ctype \
        fileinfo \
        json \
        mbstring \
        pdo_mysql \
        pdo_pgsql \
        tokenizer \
        xml


# Copy Composer binary from the Composer official Docker image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV WEB_DOCUMENT_ROOT /app/public
ENV APP_ENV production

WORKDIR /app
COPY . .

RUN composer install --no-interaction --optimize-autoloader --no-dev

COPY ./my-shell-scripts/set-staging-env.sh .
RUN chmod 777 set-staging-env.sh


RUN php artisan passport:keys


WORKDIR /opt/docker/provision/entrypoint.d 
RUN echo "sh /app/set-staging-env.sh" >> 20-nginx.sh