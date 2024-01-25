<?php

// API
require_once "vendor/autoload.php";
require_once "bootstrap.php";

// load conf. it from ini file
// $configFile = __DIR__ . "/config/mama-config.ini";
// if (! file_exists ( $configFile )) {
// $copySuccess = copy ( $configFile . ".sample", $configFile );
// }
// $ini_array = parse_ini_file ( $configFile, true );

$cron_daily_mailler = $ini_array ['cron'] ['daily_mailler'];
$cron_daily_mailler_log = $ini_array ['cron'] ['daily_mailler_log'];
$cron_weekly_mailler = $ini_array ['cron'] ['weekly_mailler'];
$cron_weekly_mailler_log = $ini_array ['cron'] ['weekly_mailler_log'];
$cron_monthly_users_inactiver = $ini_array ['cron'] ['monthly_users_inactiver'];
$cron_monthly_users_inactiver_log = $ini_array ['cron'] ['monthly_users_inactiver_log'];
//
// Add this line to your crontab file:
//
// * * * * * cd /path/to/project && php jobby.php 1>> /dev/null 2>&1
//

$jobby = new \Jobby\Jobby ();

$jobby->add ( 'DailyMail', array (
		// cmd
		// 'command' => 'php cron_jobs/dailyMailler.php"',
		'command' => function () {
			echo `php cron_jobs/dailyMailler.php`;
			return true;
		},
		// each day
		'schedule' => $cron_daily_mailler,
		'output' => $cron_daily_mailler_log,
		'enabled' => true 
) );

$jobby->add ( 'WeeklyMail', array (
		'command' => function () {
			echo `php cron_jobs/weeklyMailler.php`;
			return true;
		},
		// each monday at 8 am
		'schedule' => $cron_weekly_mailler,
		'output' => $cron_weekly_mailler_log,
		'enabled' => true 
) );

$jobby->add ( 'MonthlyUsersInativer', array (
		'command' => function () {
			echo `php cron_jobs/monthlyUsersInactiver.php`;
			return true;
		},
		// each first monday of the month
		'schedule' => $cron_monthly_users_inactiver,
		'output' => $cron_monthly_users_inactiver_log,
		'enabled' => true 
) );

$jobby->run ();
