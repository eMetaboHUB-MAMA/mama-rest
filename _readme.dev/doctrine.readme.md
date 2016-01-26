create db
  vendor/bin/doctrine orm:schema-tool:create

drop
  vendor/bin/doctrine orm:schema-tool:drop --force
 
update
  vendor/bin/doctrine orm:schema-tool:update --force
 
 
http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html
https://doctrine-orm.readthedocs.org/en/latest/tutorials/getting-started.html