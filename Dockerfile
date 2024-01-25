FROM metabohub/mama-core:1.2.0

# set author
LABEL maintainer="nils.paulhe@inrae.fr"

# [php] init composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" &&\
    php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" &&\
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

# [final] restart apache2
RUN echo "service apache2 start &&\
        service memcached start &&\
        tail -f /var/log/apache2/*.log" >> /startup.sh &&\
    chmod +x /startup.sh

CMD ["/bin/bash", "-c", "/startup.sh"]

# [END]
