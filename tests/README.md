
@see https://phpunit.de/getting-started.html

```bash
    # get phpunit.phar
    wget https://phar.phpunit.de/phpunit.phar
    # test version
    php phpunit.phar --version
    # run test
    php phpunit.phar --bootstrap ../vendor/autoload.php userTest
    # NOTE: you can also install it with `sudo apt-get install phpunit` 
    # then run `phpunit --bootstrap ../vendor/autoload.php userTest`
```