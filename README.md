MAMA - REST
=======

Metadata
-----------

 * **@name**: MAMA - REST
 * **@version**: 1.0
 * **@authors**: <nils.paulhe@inra.fr>, <franck.giacomoni@inra.fr>
 * **@date creation**: 2016/01/26
 * **@main usage**: project use to host all MAMA's REST webservices methods.

Configuration
-----------

Note: the command examples are all for a **debian** system with **apache**.

### Requirement:

 * Ubuntu server 14.04+ or CentOS 7+
 * php 5.5+ (`sudo apt-get install php`)
 * php-apc, php-mcrypt, php-ldap (`sudo apt-get install php-apc php-mcrypt php-ldap && sudo php5enmod mcrypt`)
 * MySQL 5+ or PostgreSQL 9+
 * apache 2+ or nginx 1.9+ (you need to enable rewrite ruls, cf next section)
 * composer in order to init your project with these frameworks
   * `slim` (PHP micro framework to write simple web applications and APIs)
   * `doctrine` (database storage and object mapping based on Object Relational Mapper (ORM) and the Database Abstraction Layer (DBAL) concepts) 
   * `jobbyphp` (add cron expression to your PHP project [view on github](https://github.com/jobbyphp/jobby))
   * `phpmailer` (send emails [view on github](https://github.com/PHPMailer/PHPMailer))
   * `phpexcel` (create XLS files [view on website](https://packagist.org/packages/phpoffice/phpexcel))
 * developpers: unit tests require PHPUnit 3.7+ (`sudo apt-get install phpunit`)

### Rewrite Rules:

For apache: ` sudo a2enmod rewrite && sudo service apache2 restart` is fine; then add this config to your `apache2.conf` file:
```
<Directory /var/www/html/mama-rest/>
  AllowOverride All
</Directory>
```

For nginx or other web-server configuration (HipHop Virtual Machine, IIS, lighttpd) please refer to [slim3 online documentation](http://www.slimframework.com/docs/start/web-servers.html).

### Security:

In order to be sur that users only access to `public/` folder's scripts, you must set it as your webapp root in your web-server configuration, then make an alias from `http://server-name/optiona-webapp-name` to this root.

### Deploy:
 * get project data `git clone ssh://git@pfemw3.clermont.inra.fr:dev-team/mama-rest.git`
 * you propably should set the owner of all files to your web-server unix user/group (e.g. for apache: `chown -R www-data:www-data mama-rest`)
 * download in install `slim`, `doctrine`, `jobbyphp`, `phpmailer` and `phpexcel` with `composer`: `cd /tmp/ && curl -sS https://getcomposer.org/installer | php && cd /dir/to/folder/mama-rest && php /tmp/composer.phar update `
 * init cron by adding this rule to your crontab list: `* * * * * cd /dir/to/folder/mama-rest && php jobby.php 1>> /dev/null 2>&1` (you should use apache user's crontab)
 * set ownership of `uploaded_files` folder to apache (chown command)
 * set server timezone to UTC (for Ubuntu server 16.04 enter `sudo  timedatectl set-timezone Etc/UTC​`)
 * WARNING init the database before going any further

### Database initialization:

#### prod:

 1. create `config/mama-config.ini` file form `config/mama-config.ini.sample` template (e.g. `cp config/mama-config.ini.sample config/mama-config.ini`)
 2. edit `config/mama-config.ini` with your database informations
 3. create your database and database-users (c.f. SQL bottom code) matching your ini file.
 4. run `vendor/bin/doctrine orm:schema-tool:create` command in order to init the database tables (from project's root)
 
```sql
    create database YOUR_DATABASE_NAME character set utf8;
    CREATE USER 'YOUR_DATABASE_USER'@'localhost' IDENTIFIED BY 'ENTER_A_STRONG_PASSOWRD';
    GRANT ALL PRIVILEGES ON YOUR_DATABASE_NAME.* TO 'YOUR_DATABASE_USER'@'localhost';
    FLUSH PRIVILEGES;
```

#### dev & tests:
only for developpers on local computers.

 1. create `test/mama-test.ini` file form `config/mama-test.ini.sample` template (e.g. `cp config/mama-test.ini.sample config/mama-test.ini`)
 2. edit `config/mama-test.ini` with your database informations
 3. create your database and database-users (c.f. SQL bottom code) matching your ini file.
 4. run `../vendor/bin/doctrine orm:schema-tool:create` command in order to init the database tables (from project's `test` folder)

```sql
    create database YOUR_TEST_DATABASE_NAME character set utf8;
    CREATE USER 'YOUR_TEST_DATABASE_USER'@'localhost' IDENTIFIED BY 'ENTER_A_STRONG_PASSOWRD';
    GRANT ALL PRIVILEGES ON YOUR_DATABASE_TEST_NAME.* TO 'YOUR_DATABASE_TEST_USER'@'localhost';
    FLUSH PRIVILEGES;
```

notes: 
 - if you update the data-model, refactor these changes in the database with `vendor/bin/doctrine orm:schema-tool:update --force` command
 - see: http://stackoverflow.com/questions/19066140/doctrine-error-failed-opening-required-tmp-cg-source-php
    - `sudo -u www-data vendor/bin/doctrine orm:generate-proxies`
    - `chown www-data:www-data /tmp/__*`
 
### Warning:
The password in database are hashed with a salt; this salt is randomly generated at server first start and stored in the `config/salt.txt` file.
The database connexion settings are in the `config/mama-config.ini` file. If the file does not exists, the web-application copy it from `config/mama-config.ini.sample` template file
In order to improve preformances, the database connexion parameters and the salt are stored in the server RAM (random access memory).
So if your deleted the salt file or edit the `mama-config.ini` file, you must restart the web-server (e.g. with `service apache restart`)

### Unit test:

Services provided
-----------

 * Manage MetaboHUB's main protal new Analysis Requets and Users.
 * This web-application is Restfull, support `get`, `post`, `put`, `delete` verbes and `text`, `json`, `xml` outputs.

Technical description
-----------

For developpers:
 * test all PHP methods in service with PHPUnit (e.g. `phpunit --bootstrap ../vendor/autoload.php tokenManagementServiceTest.php`)
 * test REST requests with curl (e.g. `curl -X POST --data "email=nils.paulhe@gmail.com&password=toto" -i -H "Accept: application/json" http://localhost/mama-rest/public/user`)

Notes
-----------

Where the app. is in dev / prod.

License 
-----------

(C) Copyright - MetaboHUB 2016
