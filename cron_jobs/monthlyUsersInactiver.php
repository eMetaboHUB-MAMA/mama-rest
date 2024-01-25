<?php

// prevent bug #95
$_SERVER['HTTP_HOST'] = $argv[1];

// API
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../bootstrap.php";

// MAMA API
require_once __DIR__ . "/../api/services/userManagementService.php";
require_once __DIR__ . "/../api/services/emailManagementService.php";

echo "[" . date ( "Y-m-d H:i:s" ) . "] set status 'invative' to users not active since 2 years -> start \n";

// list users with not active since 2 years
$date = new \DateTime ( "-2 year" );

// case 1 week (test):
// $date = new \DateTime ( "-1 week" );

$users = UserManagementService::fetchNotActiveUsers ( $date );
$count = 0;

// for each user, inactive them
foreach ( $users as $k => $user ) {
	
	echo "[" . date ( "Y-m-d H:i:s" ) . "] user '" . $user->getLogin () . "' not active the last 2 years. \n";
	
	// for each user, -> set status to inactive
	UserManagementService::setToInactive ( $user );
	
	// // send digest email
	// EmailManagementService::sendDailyNotificationsEmail ( $user );
}

echo "[" . date ( "Y-m-d H:i:s" ) . "] set status 'invative' to " . $count . " user(s) not active since 2 years -> end \n";
exit ( 0 );
