<?php

// prevent bug #95
$_SERVER['HTTP_HOST'] = $argv[1];
$_MAMA_SERVER_HTTP_HOST = $argv[1];

// API
require_once "vendor/autoload.php";
require_once "bootstrap.php";

//
// Add this line to your crontab file:
//
// * * * * * cd /path/to/project && php jobby.php $_MAMA_SERVER_HTTP_HOST >> /dev/null 2>&1
//

$jobby = new \Jobby\Jobby();

$jobby->add('DailyMail', array(
	// cmd
	// 'command' => 'php cron_jobs/dailyMailler.php"',
	'command' => function () {
		echo `php cron_jobs/dailyMailler.php $_MAMA_SERVER_HTTP_HOST`;
		return true;
	},
	// each day
	'schedule' => $cron_daily_mailler,
	'output' => $cron_daily_mailler_log,
	'enabled' => true
));

$jobby->add('WeeklyMail', array(
	'command' => function () {
		echo `php cron_jobs/weeklyMailler.php $_MAMA_SERVER_HTTP_HOST`;
		return true;
	},
	// each monday at 8 am
	'schedule' => $cron_weekly_mailler,
	'output' => $cron_weekly_mailler_log,
	'enabled' => true
));

$jobby->add('MonthlyUsersInativer', array(
	'command' => function () {
		echo `php cron_jobs/monthlyUsersInactiver.php $_MAMA_SERVER_HTTP_HOST`;
		return true;
	},
	// each first monday of the month
	'schedule' => $cron_monthly_users_inactiver,
	'output' => $cron_monthly_users_inactiver_log,
	'enabled' => true
));

$jobby->add('MonthlyUsersAnonymizer', array(
	'command' => function () {
		echo `php cron_jobs/monthlyUsersAnonymizer.php $_MAMA_SERVER_HTTP_HOST`;
		return true;
	},
	// each first monday of the month
	'schedule' => $cron_monthly_users_inactiver,
	'output' => $cron_monthly_users_inactiver_log,
	'enabled' => true
));

$jobby->run();