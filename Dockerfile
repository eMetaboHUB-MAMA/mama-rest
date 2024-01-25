FROM metabohub/mama-core

# set author
MAINTAINER Nils Paulhe <nils.paulhe@inrae.fr>

# [php] copy composer config. files
COPY ./composer.* /var/www/html/

# [php] init composer
RUN cd /tmp/ &&\
    curl -sS https://getcomposer.org/installer | php &&\
    cd /var/www/html/ &&\
    php /tmp/composer.phar update &&\
    /tmp/composer.phar require phpunit/php-code-coverage &&\
    rm -rf /tmp/*

# [apache2] copy apache2 config.
COPY ./docker-conf/apache2.conf /etc/apache2/apache2.conf
COPY ./docker-conf/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# [php] copy MAMA-REST directoy
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
RUN echo "service apache2 start && tail -f /var/log/apache2/*.log" >> /startup.sh &&\
    chmod +x /startup.sh

CMD ["/bin/bash", "-c", "/startup.sh"]

# [END]
