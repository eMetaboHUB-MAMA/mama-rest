<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";
require_once(__DIR__ . "/api/utils/Host_Memcached.class.php");

// init memcached
$memcacheD = new Host_Memcached();

// Load entity configuration from PHP file annotations
// This is the most versatile mode, I advise using it!
// If you don't like it, Doctrine also supports YAML or XML
$isDevMode = false;

$entityManager = null;

$app_webapp_url = null;

$database_driver = null;
$database_host = null;
$database_dbname = null;
$database_user = null;
$database_password = null;

$ldap_server = null;
$ldap_filter = null;
$ldap_identifier = null;

$smtp_host = null;
$smtp_smtpauth = null;
$smtp_username = null;
$smtp_password = null;
$smtp_secure = null;
$smtp_port = null;
$smtp_from_email = null;
$smtp_from_displayname = null;
$smtp_replyto_email = null;
$smtp_replyto_displayname = null;

$projects_files_dir = null;

$cron_daily_mailler_log = null;
$cron_weekly_mailler_log = null;
$cron_monthly_users_inactiver_log = null;

// mama#39 - contact form
$contact_email = null;
$contact_name = null;

// check RAM
if ($memcacheD->get("database_driver")) {
    $app_webapp_url = $memcacheD->get("app_webapp_url");

    $database_driver = $memcacheD->get("database_driver");
    $database_host = $memcacheD->get("database_host");
    $database_dbname = $memcacheD->get("database_dbname");
    $database_user = $memcacheD->get("database_user");
    $database_password = $memcacheD->get("database_password");

    $ldap_server = $memcacheD->get("ldap_server");
    $ldap_filter = $memcacheD->get("ldap_filter");
    $ldap_identifier = $memcacheD->get("ldap_identifier");

    $smtp_host = $memcacheD->get("smtp_host");
    $smtp_smtpauth = $memcacheD->get("smtp_smtpauth");
    $smtp_username = $memcacheD->get("smtp_username");
    $smtp_password = $memcacheD->get("smtp_password");
    $smtp_secure = $memcacheD->get("smtp_secure");
    $smtp_port = $memcacheD->get("smtp_port");
    $smtp_from_email = $memcacheD->get("smtp_from_email");
    $smtp_from_displayname = $memcacheD->get("smtp_from_displayname");
    $smtp_replyto_email = $memcacheD->get("smtp_replyto_email");
    $smtp_replyto_displayname = $memcacheD->get("smtp_replyto_displayname");

    $projects_files_dir = $memcacheD->get("projects_files_dir");

    $cron_daily_mailler_log = $memcacheD->get("cron_daily_mailler_log");
    $cron_weekly_mailler_log = $memcacheD->get("cron_weekly_mailler_log");
    $cron_monthly_users_inactiver_log = $memcacheD->get("cron_monthly_users_inactiver_log");

    // mama#39 - contact form
    $contact_email = $memcacheD->get("contact_email");
    $contact_name = $memcacheD->get("contact_name");
} else {

    // if not in RAM load it from ini file
    $configFile = __DIR__ . "/config/mama-config.ini";
    if (!file_exists($configFile)) {
        $copySuccess = copy($configFile . ".sample", $configFile);
    }
    $ini_array = parse_ini_file($configFile, true);

    $app_webapp_url = $ini_array['application']['webapp_url'];

    $database_driver = $ini_array['database']['driver'];
    $database_host = $ini_array['database']['host'];
    $database_dbname = $ini_array['database']['dbname'];
    $database_user = $ini_array['database']['user'];
    $database_password = $ini_array['database']['password'];

    $ldap_server = $ini_array['ldap']['server'];
    $ldap_filter = $ini_array['ldap']['filter'];
    $ldap_identifier = $ini_array['ldap']['identifier'];

    $smtp_host = $ini_array['smtp']['host'];
    $smtp_smtpauth = $ini_array['smtp']['smtpauth'];
    $smtp_username = $ini_array['smtp']['username'];
    $smtp_password = $ini_array['smtp']['password'];
    $smtp_secure = $ini_array['smtp']['secure'];
    $smtp_port = $ini_array['smtp']['port'];
    $smtp_from_email = $ini_array['smtp']['from_email'];
    $smtp_from_displayname = $ini_array['smtp']['from_displayname'];
    $smtp_replyto_email = $ini_array['smtp']['replyto_email'];
    $smtp_replyto_displayname = $ini_array['smtp']['replyto_displayname'];

    $projects_files_dir = $ini_array['other']['projects_files_dir'];

    // mama#39 - contact form
    $contact_email =  $ini_array['contact']['email'];
    $contact_name =  $ini_array['contact']['name'];

    // store in RAM
    $memcacheD->set("app_webapp_url", $app_webapp_url);

    $memcacheD->set("database_driver", $database_driver);
    $memcacheD->set("database_host", $database_host);
    $memcacheD->set("database_dbname", $database_dbname);
    $memcacheD->set("database_user", $database_user);
    $memcacheD->set("database_password", $database_password);

    // $memcacheD->set( "ldap_email_filter", $ini_array ['ldap'] ['email_filter'] );
    $memcacheD->set("ldap_server", $ldap_server);
    $memcacheD->set("ldap_filter", $ldap_filter);
    $memcacheD->set("ldap_identifier", $ldap_identifier);

    $memcacheD->set("smtp_host", $smtp_host);
    $memcacheD->set("smtp_smtpauth", $smtp_smtpauth);
    $memcacheD->set("smtp_username", $smtp_username);
    $memcacheD->set("smtp_password", $smtp_password);
    $memcacheD->set("smtp_secure", $smtp_secure);
    $memcacheD->set("smtp_port", $smtp_port);
    $memcacheD->set("smtp_from_email", $smtp_from_email);
    $memcacheD->set("smtp_from_displayname", $smtp_from_displayname);
    $memcacheD->set("smtp_replyto_email", $smtp_replyto_email);
    $memcacheD->set("smtp_replyto_displayname", $smtp_replyto_displayname);

    $memcacheD->set("projects_files_dir", $projects_files_dir);

    // mama#39 - contact form
    $memcacheD->set("contact_email", $ini_array['contact']['email']);
    $memcacheD->set("contact_name", $ini_array['contact']['name']);

    $cron_daily_mailler_log = $ini_array['cron']['daily_mailler_log'];
    $cron_weekly_mailler_log = $ini_array['cron']['weekly_mailler_log'];
    $cron_monthly_users_inactiver_log = $ini_array['cron']['monthly_users_inactiver_log'];
    $memcacheD->set("cron_daily_mailler_log", $cron_daily_mailler_log);
    $memcacheD->set("cron_weekly_mailler_log", $cron_weekly_mailler_log);
    $memcacheD->set("cron_monthly_users_inactiver_log", $cron_monthly_users_inactiver_log);
}

define("app_webapp_url", $app_webapp_url);

define("ldap_server", $ldap_server);
define("ldap_filter", $ldap_filter);
define("ldap_identifier", $ldap_identifier);

define("smtp_host", $smtp_host);
define("smtp_smtpauth", $smtp_smtpauth);
define("smtp_username", $smtp_username);
define("smtp_password", $smtp_password);
define("smtp_secure", $smtp_secure);
define("smtp_port", $smtp_port);
define("smtp_from_email", $smtp_from_email);
define("smtp_from_displayname", $smtp_from_displayname);
define("smtp_replyto_email", $smtp_replyto_email);
define("smtp_replyto_displayname", $smtp_replyto_displayname);

define("projects_files_dir", $projects_files_dir);

define("cron_daily_mailler_log", $cron_daily_mailler_log);
define("cron_weekly_mailler_log", $cron_weekly_mailler_log);
define("cron_monthly_users_inactiver_log", $cron_monthly_users_inactiver_log);

// mama#39 - contact form
define("contact_email", $contact_email);
define("contact_name", $contact_name);

// init data model
$config = Setup::createAnnotationMetadataConfiguration(array(
    __DIR__ . "/data-model"
), $isDevMode);

// Set up database connection data
$conn = array(
    'driver' => $database_driver,
    'host' => $database_host,
    'dbname' => $database_dbname,
    'user' => $database_user,
    'password' => $database_password
);

// create entity cache
$config->setAutoGenerateProxyClasses(true);
$config->setProxyDir('/tmp/' . $database_dbname);

// create EM
$entityManager = EntityManager::create($conn, $config);
$GLOBALS['entityManager'] = $entityManager;
