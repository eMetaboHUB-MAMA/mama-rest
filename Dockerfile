# call PHP 5.6 / apache + MAMA addons
FROM npaulhe/mama-config

# set author
MAINTAINER Nils Paulhe <nils.paulhe@inra.fr>

# [php] copy MAMA-REST directoy
COPY . /var/www/html/

# [php] copy apache2 config file
COPY docker-conf/apache2-mama.conf /etc/apache2/sites-enabled/mama-rest.conf
COPY docker-conf/php.ini /usr/local/etc/php/

# [MAMA] init cron jobs
#COPY docker-conf/crontab /etc/cron.d/mail-cron
#RUN chmod 0644 /etc/cron.d/mail-cron && touch /var/log/cron.log
#RUN crontab /etc/cron.d/mail-cron
#RUN cron

# [other] share volume
RUN chown -R www-data:www-data .
# VOLUME /mnt/mama_uploaded_files:/var/www/html/uploaded_files 

# [final] restart apache2
RUN service apache2 restart

# [END]