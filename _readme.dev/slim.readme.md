 php -S 0.0.0.0:8080 -t public public/index.php
 
 http://localhost:8080/users?format=json&token=adminToken
 view-source:http://localhost:8080/?format=xml&token=adminToken
 
 
 sudo a2enmod rewrite && sudo service apache2 restart
 
 
 http://www.slimframework.com/docs/start/web-servers.html