create db
  vendor/bin/doctrine orm:schema-tool:create

drop
  vendor/bin/doctrine orm:schema-tool:drop --force
 
update
  vendor/bin/doctrine orm:schema-tool:update --force
 
 
http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html
https://doctrine-orm.readthedocs.org/en/latest/tutorials/getting-started.html

# PASSWORD
sudo apt-get install php-apc php-mcrypt
sudo php5enmod mcrypt

curl -i -H "Accept: application/json" localhost:8080/
-X POST
--data "param1=value1&param2=value2"

 curl -X POST --data "email=value1&password=value2" -i -H "Accept: application/json" http://localhost/pfem/mama-rest/public/token
 
 
 curl -X POST --data "email=nils.paulhe+1@gmail.com&password=toto" -i -H "Accept: application/json" http://localhost/pfem/mama-rest/public/user
 curl -X POST --data "email=nils.paulhe+1@gmail.com&password=toto" -i -H "Accept: application/json" http://localhost/pfem/mama-rest/public/token