<?php
// cli-config.php
require_once "bootstrap.tests.php";

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet ( $entityManager );