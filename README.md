# MAMA - REST

[![pipeline status](https://services.pfem.clermont.inrae.fr/gitlab/mama/mama-rest/badges/dev/pipeline.svg)](https://services.pfem.clermont.inrae.fr/gitlab/mama/mama-rest/commits/dev)
[![coverage report](https://services.pfem.clermont.inrae.fr/gitlab/mama/mama-rest/badges/dev/coverage.svg)](https://services.pfem.clermont.inrae.fr/gitlab/mama/mama-rest/commits/dev)

## Metadata

- authors: <nils.paulhe@inrae.fr>, <franck.giacomoni@inrae.fr>
- creation date: `2016-01-26`
- main usage: project use to host all MAMA's REST webservices methods.

## Configuration

Note: the command examples are all for a **debian** system with **apache2** and **mysql**.

### Requirements

- Ubuntu server 22.04+
- php 8.1 (`sudo apt install -y php8.1`)
  - php-mcrypt, php-ldap, php-xml, ... ⇒ `sudo apt install -y php8.1-ldap php8.1-curl php8.1-xml php8.1-mysql php8.1-gd php8.1-zip && sudo phpenmod mcrypt`
  - [php-memcached](https://devdocs.magento.com/guides/v2.3/config-guide/memcache/memcache_ubuntu.html) ⇒ `sudo apt install -y memcached php-memcached`
  - for phpoffice ⇒ `sudo apt install -y php8.1-gd php8.1-mbstring php8.1-zip`
  - install mycrypt ⇒ `sudo apt -y install gcc make autoconf libc-dev pkg-config && sudo apt -y install php8.1-dev && sudo apt -y install libmcrypt-dev && sudo pecl install mcrypt`; then add `extension=mcrypt.so` in `php.ini` file
- MySQL 5+ or PostgreSQL 9+ (`sudo apt install -y mysql-server`)
- apache 2+ or nginx 1.9+ (you need to enable rewrite ruls, cf next section) (`sudo apt install -y apache2`)
- curl (`sudo apt install -y curl`)
- [composer](https://getcomposer.org/download/) in order to init your project with these frameworks
  - `slim` (PHP micro framework to write simple web applications and APIs)
  - `doctrine` (database storage and object mapping based on Object Relational Mapper (ORM) and the Database Abstraction Layer (DBAL) concepts)
  - `jobbyphp` (add cron expression to your PHP project [view on github](https://github.com/jobbyphp/jobby))
  - `phpmailer` (send emails [view on github](https://github.com/PHPMailer/PHPMailer))
  - `phpexcel` (create XLS files [view on website](https://packagist.org/packages/phpoffice/phpexcel))
- a SMTP application

### Ubuntu 22.04 / apache2 / PHP 8.1 activation

To allow apache2 to execute PHP files please run this command:

`sudo apt install libapache2-mod-php8.1 && sudo systemctl restart apache2`

### Rewrite Rules

For apache: `sudo a2enmod rewrite && sudo systemctl restart apache2` is fine; then add this config to your `apache2.conf` file:

```html
<Directory /var/www/html/mama-rest/>
  AllowOverride All
</Directory>
```

For nginx or other web-server configuration (HipHop Virtual Machine, IIS, lighttpd) please refer to [slim3 online documentation](http://www.slimframework.com/docs/start/web-servers.html).

### Security

In order to be sur that users only access to `public/` folder's scripts, you must set it as your webapp root in your web-server configuration, then make an alias from `http://server-name/optiona-webapp-name` to this root.

### Deploy

- get project data `git clone https://services.pfem.clermont.inrae.fr/gitlab/mama/mama-rest.git`
- you propably should set the owner of all files to your web-server unix user/group (e.g. for apache: `chown -R www-data:www-data mama-rest`)
- download in install `slim`, `doctrine`, `jobbyphp`, `phpmailer` and `phpexcel` with `composer`: `cd /tmp/ && curl -sS https://getcomposer.org/installer | php && cd /dir/to/folder/mama-rest && sudo -u www-data php /tmp/composer.phar update`
- init cron by adding this rule to your crontab list: `* * * * * cd /dir/to/folder/mama-rest && php jobby.php 1>> /dev/null 2>&1` (you should use apache user's crontab)
- set ownership of `uploaded_files` folder to apache (chown command)
- set server timezone to UTC (for Ubuntu server >=16.04 enter `sudo  timedatectl set-timezone Etc/UTC​`)
- WARNING init the database before going any further

## Database initialization

### prod

 1. create `config/mama-config.ini` file form `config/mama-config.ini.sample` template (e.g. `cp config/mama-config.ini.sample config/mama-config.ini`)
 2. edit `config/mama-config.ini` with your database informations
 3. create your database and database-users (c.f. SQL bottom code) matching your ini file.
 4. run `vendor/bin/doctrine orm:schema-tool:create` command in order to init the database tables (from project's root)

```sql
    CREATE DATABASE `your-database-name` CHARACTER SET UTF8;
    CREATE USER 'your-database-user'@'localhost' IDENTIFIED BY 'enter-a-strong-password';
    GRANT ALL PRIVILEGES ON `your-database-name`.* TO 'your-database-user'@'localhost';
    FLUSH PRIVILEGES;
```

To init the first database's users as admin:

```sql
show databases;
use your-database-name;
show tables;
select users.login from users;
update users set user_status=10, user_right=520 where users.id=X; # replace X by targeted users' ID
```

### dev & tests

Only for developpers on local computers.

 1. create `test/mama-test.ini` file form `test/mama-test.ini.sample` template (e.g. `cp tests/mama-test.ini.sample tests/mama-test.ini`)
 2. edit `test/mama-test.ini` with your database informations
 3. create your database and database-users (c.f. SQL bottom code) matching your ini file.
 4. run `../vendor/bin/doctrine orm:schema-tool:create` command in order to init the database tables (from project's `test` folder)

```sql
    CREATE DATABASE `your-test-database-name` CHARACTER SET UTF8;
    CREATE USER 'your-test-database-user'@'localhost' IDENTIFIED BY 'enter-a-strong-password';
    GRANT ALL PRIVILEGES ON `your-test-database-name`.* TO 'your-test-database-user'@'localhost';
    FLUSH PRIVILEGES;
```

notes:

- if you update the data-model, refactor these changes in the database with `vendor/bin/doctrine orm:schema-tool:update --force` command.
- see: `http://stackoverflow.com/questions/19066140/doctrine-error-failed-opening-required-tmp-cg-source-php`
  - `sudo -u www-data vendor/bin/doctrine orm:generate-proxies`
  - `chown www-data:www-data /tmp/__*`

You can build a test docker image to developp:

```bash
# build ref. image (base)
cd /path/to/mama-management && docker build -t metabohub/mama-core .
cd /path/to/mama-rest && docker build -t metabohub/mama-rest .

# build test image (add mysql, dev tools)
cd /path/to/mama-rest && docker build -t metabohub/mama-rest-tests -f Dockerfile-tests .

# run with shared volum for work. dir.
docker run --rm -it \
   -v $(pwd):/var/www/html_dev/ \
   -p 8888:80 \
   --name mama-rest-tests \
   metabohub/mama-rest-tests

# update live code into container
docker exec -it mama-rest-tests bash -c "cp -r /var/www/html_dev/* /var/www/html/"
```

Then edit `MAMA - WebApp` config file `` like this:

```json
{
   "serverLanguage":"php",
   "mamaRestApi":"http://localhost:8888/",
   "defaultLang":"en"
}
```

## Warning

The database connexion settings are in the `config/mama-config.ini` file. If the file does not exists, the web-application copy it from `config/mama-config.ini.sample` template file
In order to improve preformances, the database connexion parameters are stored in the server RAM (random access memory).
So if you edit the `mama-config.ini` file, you must restart the web-server (e.g. with `service apache restart`)

## Unit tests

Developpers: unit tests require PHPUnit 5.1.3+.\
:warning: do not forget to initialize the test database (see this readme file, `database → dev & tests` section).\
To run test you need to install the following tools:

- PHP-Unit `apt install phpunit`
- PHP-dev `apt install php8.1-dev apt-utils`
- xdebug: ``pecl install xdebug && echo "zend_extension=/usr/lib/php/20190902/xdebug.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`;``
- PHP-Code-Coverage `/tmp/composer.phar require phpunit/php-code-coverage`

To run all tests and check test code coverage:

```bash
cd /path/to/mama-rest
cd tests
phpunit --coverage-text --colors=never  --bootstrap ../vendor/autoload.php .
```

To test a single service:

```bash
phpunit --bootstrap ../vendor/autoload.php tokenManagementServiceTest.php
```

Functionnal tests: test REST requests with curl\
(e.g. `curl -X POST --data "email=my-email@domain.dns&password=XXXXXXX" -i -H "Accept: application/json" http://localhost/mama-rest/public/user`)

## Services provided

- Manage MetaboHUB's main protal new Analysis Requets and Users.
- This web-application is Restfull, support `get`, `post`, `put`, `delete` verbes and `text`, `json`, `xml` outputs.

<!--
## Notes

Where the app. is in dev / prod.
-->

## Docker

Please refer to [this specific documentation](docker-conf/howto.md).

## License

(C) Copyright - MetaboHUB 2016.\

MAMA "in-house" code is provided under [MIT license](LICENSE.md).
