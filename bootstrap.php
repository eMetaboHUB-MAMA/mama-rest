<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

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

$cron_daily_mailler_log = null;
$cron_weekly_mailler_log = null;
$cron_monthly_users_inactiver_log = null;

// check RAM
if (apc_exists ( "database_driver" )) {
	$app_webapp_url = apc_fetch ( "app_webapp_url" );
	
	$database_driver = apc_fetch ( "database_driver" );
	$database_host = apc_fetch ( "database_host" );
	$database_dbname = apc_fetch ( "database_dbname" );
	$database_user = apc_fetch ( "database_user" );
	$database_password = apc_fetch ( "database_password" );
	
	$smtp_host = apc_fetch ( "smtp_host" );
	$smtp_smtpauth = apc_fetch ( "smtp_smtpauth" );
	$smtp_username = apc_fetch ( "smtp_username" );
	$smtp_password = apc_fetch ( "smtp_password" );
	$smtp_secure = apc_fetch ( "smtp_secure" );
	$smtp_port = apc_fetch ( "smtp_port" );
	$smtp_from_email = apc_fetch ( "smtp_from_email" );
	$smtp_from_displayname = apc_fetch ( "smtp_from_displayname" );
	$smtp_replyto_email = apc_fetch ( "smtp_replyto_email" );
	$smtp_replyto_displayname = apc_fetch ( "smtp_replyto_displayname" );
	
	$cron_daily_mailler_log = apc_fetch ( "cron_daily_mailler_log" );
	$cron_weekly_mailler_log = apc_fetch ( "cron_weekly_mailler_log" );
	$cron_monthly_users_inactiver_log = apc_fetch ( "cron_monthly_users_inactiver_log" );
} else {
	
	// if not in RAM load it from ini file
	$configFile = __DIR__ . "/config/mama-config.ini";
	if (! file_exists ( $configFile )) {
		$copySuccess = copy ( $configFile . ".sample", $configFile );
	}
	$ini_array = parse_ini_file ( $configFile, true );
	
	$app_webapp_url = $ini_array ['application'] ['webapp_url'];
	
	$database_driver = $ini_array ['database'] ['driver'];
	$database_host = $ini_array ['database'] ['host'];
	$database_dbname = $ini_array ['database'] ['dbname'];
	$database_user = $ini_array ['database'] ['user'];
	$database_password = $ini_array ['database'] ['password'];
	
	$smtp_host = $ini_array ['smtp'] ['host'];
	$smtp_smtpauth = $ini_array ['smtp'] ['smtpauth'];
	$smtp_username = $ini_array ['smtp'] ['username'];
	$smtp_password = $ini_array ['smtp'] ['password'];
	$smtp_secure = $ini_array ['smtp'] ['secure'];
	$smtp_port = $ini_array ['smtp'] ['port'];
	$smtp_from_email = $ini_array ['smtp'] ['from_email'];
	$smtp_from_displayname = $ini_array ['smtp'] ['from_displayname'];
	$smtp_replyto_email = $ini_array ['smtp'] ['replyto_email'];
	$smtp_replyto_displayname = $ini_array ['smtp'] ['replyto_displayname'];
	
	// store in RAM
	apc_store ( "app_webapp_url", $app_webapp_url );
	
	apc_store ( "database_driver", $database_driver );
	apc_store ( "database_host", $database_host );
	apc_store ( "database_dbname", $database_dbname );
	apc_store ( "database_user", $database_user );
	apc_store ( "database_password", $database_password );
	
	// apc_store ( "ldap_email_filter", $ini_array ['ldap'] ['email_filter'] );
	apc_store ( "ldap_server", $ini_array ['ldap'] ['server'] );
	apc_store ( "ldap_filter", $ini_array ['ldap'] ['filter'] );
	apc_store ( "ldap_identifier", $ini_array ['ldap'] ['identifier'] );
	
	apc_store ( "smtp_host", $smtp_host );
	apc_store ( "smtp_smtpauth", $smtp_smtpauth );
	apc_store ( "smtp_username", $smtp_username );
	apc_store ( "smtp_password", $smtp_password );
	apc_store ( "smtp_secure", $smtp_secure );
	apc_store ( "smtp_port", $smtp_port );
	apc_store ( "smtp_from_email", $smtp_from_email );
	apc_store ( "smtp_from_displayname", $smtp_from_displayname );
	apc_store ( "smtp_replyto_email", $smtp_replyto_email );
	apc_store ( "smtp_replyto_displayname", $smtp_replyto_displayname );
	
	apc_store ( "projects_files_dir", $ini_array ['other'] ['projects_files_dir'] );
	
	$cron_daily_mailler_log = $ini_array ['cron'] ['daily_mailler_log'];
	$cron_weekly_mailler_log = $ini_array ['cron'] ['weekly_mailler_log'];
	$cron_monthly_users_inactiver_log = $ini_array ['cron'] ['monthly_users_inactiver_log'];
	apc_store ( "cron_daily_mailler_log", $cron_daily_mailler_log );
	apc_store ( "cron_weekly_mailler_log", $cron_weekly_mailler_log );
	apc_store ( "cron_monthly_users_inactiver_log", $cron_monthly_users_inactiver_log );
}

define ( "app_webapp_url", $app_webapp_url );

// define ( "ldap_email_filter", apc_fetch ( "ldap_email_filter" ) );
define ( "ldap_server", apc_fetch ( "ldap_server" ) );
define ( "ldap_filter", apc_fetch ( "ldap_filter" ) );
define ( "ldap_identifier", apc_fetch ( "ldap_identifier" ) );

define ( "smtp_host", $smtp_host );
define ( "smtp_smtpauth", $smtp_smtpauth );
define ( "smtp_username", $smtp_username );
define ( "smtp_password", $smtp_password );
define ( "smtp_secure", $smtp_secure );
define ( "smtp_port", $smtp_port );
define ( "smtp_from_email", $smtp_from_email );
define ( "smtp_from_displayname", $smtp_from_displayname );
define ( "smtp_replyto_email", $smtp_replyto_email );
define ( "smtp_replyto_displayname", $smtp_replyto_displayname );

define ( "projects_files_dir", apc_fetch ( "projects_files_dir" ) );

define ( "cron_daily_mailler_log", $cron_daily_mailler_log );
define ( "cron_weekly_mailler_log", $cron_weekly_mailler_log );
define ( "cron_monthly_users_inactiver_log", $cron_monthly_users_inactiver_log );

// init data model
$config = Setup::createAnnotationMetadataConfiguration ( array (
		__DIR__ . "/data-model" 
), $isDevMode );

// Set up database connection data
$conn = array (
		'driver' => $database_driver,
		'host' => $database_host,
		'dbname' => $database_dbname,
		'user' => $database_user,
		'password' => $database_password 
);

// create entity cache
$config->setAutoGenerateProxyClasses ( true );

// create EM
$entityManager = EntityManager::create ( $conn, $config );





