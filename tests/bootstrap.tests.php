<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "../vendor/autoload.php";
require_once(__DIR__ . "/../api/utils/Host_Memcached.class.php");

// init memcached
$memcacheD = new Host_Memcached();

// Load entity configuration from PHP file annotations
// This is the most versatile mode, I advise using it!
// If you don't like it, Doctrine also supports YAML or XML
$isDevMode = true;

// if not in RAM load it from ini file
$configFile = __DIR__ . "/mama-test.ini";
if (!file_exists($configFile)) {
	$copySuccess = copy($configFile . ".sample", $configFile);
}
$ini_array = parse_ini_file($configFile, true);

// init data model
$config = Setup::createAnnotationMetadataConfiguration(array(
	__DIR__ . "/../data-model"
), $isDevMode);

// Set up database connection data
$conn = array(
	'driver' => $ini_array['database']['driver'],
	'host' => $ini_array['database']['host'],
	'dbname' => $ini_array['database']['dbname'],
	'user' => $ini_array['database']['user'],
	'password' => $ini_array['database']['password']
);

define("ldap_email_filter", $ini_array['ldap']['email_filter']);
define("ldap_server", $ini_array['ldap']['server']);
define("ldap_filter", $ini_array['ldap']['filter']);
define("ldap_identifier", $ini_array['ldap']['identifier']);

define("smtp_host", $ini_array['smtp']['host']);
define("smtp_smtpauth", $ini_array['smtp']['smtpauth']);
define("smtp_username", $ini_array['smtp']['username']);
define("smtp_password", $ini_array['smtp']['password']);
define("smtp_secure", $ini_array['smtp']['secure']);
define("smtp_port", $ini_array['smtp']['port']);
define("smtp_from_email", $ini_array['smtp']['from_email']);
define("smtp_from_displayname", $ini_array['smtp']['from_displayname']);
define("smtp_replyto_email", $ini_array['smtp']['replyto_email']);
define("smtp_replyto_displayname", $ini_array['smtp']['replyto_displayname']);

define("app_webapp_url",  $ini_array['application']['webapp_url']);

define("contact_email", $ini_array['contact']['email']);
define("contact_name", $ini_array['contact']['name']);

$entityManager = EntityManager::create($conn, $config);
$GLOBALS['entityManager'] = $entityManager;
