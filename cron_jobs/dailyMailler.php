<?php

// prevent bug #95
$_SERVER['HTTP_HOST'] = $argv[1];

// API
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../bootstrap.php";

// MAMA API
require_once __DIR__ . "/../api/services/userManagementService.php";
require_once __DIR__ . "/../api/services/emailManagementService.php";

echo "[" . date ( "Y-m-d H:i:s" ) . "] send daily digest email -> start \n";

// list users with daily email option

$users = UserManagementService::fetchEmailUsers ( User::$EMAIL_NOTIFICATION_DAILY );
// for each user, build daily digest email
foreach ( $users as $k => $user ) {
	
	// fetch basic user data
	$email = $user->getEmail ();
	$fullName = $user->getFirstName () . " " . $user->getLastName ();
	$lang = $user->getEmailLanguage ();
	
	// log
	echo "processing " . $email . " (email lang: " . $lang . " ) \n";
	
	// send digest email
	EmailManagementService::sendDailyNotificationsEmail ( $user );
}

echo "[" . date ( "Y-m-d H:i:s" ) . "] send daily digest email -> end \n";
exit ( 0 );