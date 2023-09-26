FROM php:apache

WORKDIR /var/www

#Instalamos prerequisitos para composer
RUN apt-get update && \
     apt-get install -y zip \
                curl \
                zip \
                unzip

COPY ./php/composer.json /var/www/


#Instalamos composer como script ejecutable desde bin
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


#Instalamos PDO y el driver de mysql para poder utilizar la conexion con la base de datos
RUN docker-php-ext-install mysqli pdo pdo_mysql \
     && docker-php-ext-enable pdo_mysql && a2enmod rewrite

#instalamos en la carpeta vendor del container. Por desgracia, necesitaremos pasar la carpeta vendor a mano desde el container
#a la PC si queremos autocompletado desde el IDE de librerias externas.
RUN composer install