FROM metabohub/mama-core:1.2.0

# set author
LABEL maintainer="nils.paulhe@inrae.fr"

# [php] init composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" &&\
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" &&\
    php composer-setup.php                    &&\
    php -r "unlink('composer-setup.php');"    &&\
    mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html/

# [php] copy composer config. files
COPY ./composer.* /var/www/html/

RUN composer update

# [apache2] copy apache2 config.
COPY ./docker-conf/apache2.conf /etc/apache2/apache2.conf
COPY ./docker-conf/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# [php] set correct ownership on files
COPY . /var/www/html/

# [MAMA] init cron jobs
#COPY docker-conf/crontab /etc/cron.d/mail-cron
#RUN chmod 0644 /etc/cron.d/mail-cron && touch /var/log/cron.log
#RUN crontab /etc/cron.d/mail-cron
#RUN cron

# [other] share volume
RUN chown -R www-data:www-data /var/www/html/
# VOLUME /mnt/mama_uploaded_files:/var/www/html/uploaded_files 

# [other] dir for proxy entities
RUN mkdir -p /tmp/mama_db
RUN chown -R www-data:www-data /tmp/mama_db

# [final] restart apache2
RUN echo "service apache2 start &&\
        service memcached start &&\
        tail -f /var/log/apache2/*.log" >> /startup.sh &&\
    chmod +x /startup.sh

CMD ["/bin/bash", "-c", "/startup.sh"]

# [END]
