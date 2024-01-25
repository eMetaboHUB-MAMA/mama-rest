<?php

// prevent bug #95
$_SERVER['HTTP_HOST'] = $argv[1];

// API
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../bootstrap.php";

// MAMA API
require_once __DIR__ . "/../api/services/gdprManagementService.php";
require_once __DIR__ . "/../api/services/emailManagementService.php";

echo "[" . date("Y-m-d H:i:s") . "] set status 'anonymized' to long-term inactive users -> start \n";

// action first (filter 61 months)
$usersToAnonymise = GdprManagementService::getUsersThatCanBeAnonymized(Trigger::Action);
$countToAnonymise = 0;
// for each user, Anonymise them
foreach ($usersToAnonymise as $k => $user) {
	if (GdprManagementService::canUserBeAnonymized($user->getId())) {
		echo "[" . date("Y-m-d H:i:s") . "] user '" . $user->getLogin() . "' will be anonymized. \n";
		// for each user, -> set status to anonymize
		GdprManagementService::anonymizeUser($user->getId());
	}
}

// warn then (filter: 60 months / 5 years)
$usersWarn = GdprManagementService::getUsersThatCanBeAnonymized(Trigger::Warn);
$countWarn = 0;
// for each user, warn them
foreach ($usersWarn as $k => $user) {
	if (GdprManagementService::canUserBeAnonymized($user->getId())) {
		echo "[" . date("Y-m-d H:i:s") . "] user '" . $user->getLogin() . "' will have an 'anonymization warning' email . \n";
		// for each user, -> send an email
		EmailManagementService::sendWarningAnonymizationEmail($user);
	}
}

echo "[" . date("Y-m-d H:i:s") . "] set status 'anonymized' to long-term inactive users -> end \n";
exit(0);