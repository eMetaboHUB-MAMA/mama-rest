<?php
session_start();

// special case: PUT
parse_str(file_get_contents("php://input"), $putData);

// API
require_once '../vendor/autoload.php';

// UTILS
require_once '../api/utils/format.php';
require_once '../api/security/tokenManagementService.php';

// MAMA OBJECTS
require_once "../bootstrap.php";
require_once '../api/services/mamaManagementService.php';
require_once '../api/services/userManagementService.php';
require_once '../api/services/projectManagementService.php';
require_once '../api/services/projectExtraDataManagementService.php';
require_once '../api/services/emailManagementService.php';
require_once '../api/services/eventManagementService.php';
require_once '../api/services/messageManagementService.php';
require_once '../api/services/appointmentManagementService.php';

require_once '../api/services/keywordManagementService.php';// also for sub-keywords and mgmer-keywords
require_once '../api/services/mthPlatformManagementService.php';

require_once '../api/services/statisticManagementService.php';

require_once '../email_jobs/specialEventMailler.php';

// mama#65
require_once '../api/services/mthSubPlatformManagementService.php';
// mama#84
require_once '../api/services/gdprManagementService.php'; 

// init
$app = new Slim\App();

// //////////////////////////////////////////////////////////////////////////////////////////////
// INFOS
$app->get('/', function ($request, $response, $args) {

	// init response
	$data = getMamaInfos();

	return formatResponse($request, $response, $args, $data);
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// TOKEN
require_once '__token_methods.php';

// //////////////////////////////////////////////////////////////////////////////////////////////
// USERS
require_once '__user_methods.php';

// //////////////////////////////////////////////////////////////////////////////////////////////
// PROJECTS
require_once '__project_methods.php';

// //////////////////////////////////////////////////////////////////////////////////////////////
// EVENTS
require_once '__event_methods.php';

// //////////////////////////////////////////////////////////////////////////////////////////////
// MESSAGES & apointments
require_once '__message_methods.php';
require_once '__appointment_methods.php';

// //////////////////////////////////////////////////////////////////////////////////////////////
// STATISTICS
require_once '__statistic_methods.php';

// //////////////////////////////////////////////////////////////////////////////////////////////
// EVENTS
require_once '__dashboard_methods.php';

// //////////////////////////////////////////////////////////////////////////////////////////////
// OTHER
require_once '__admin_methods.php';
require_once '__contact_methods.php';
require_once '__gdpr_methods.php'; // mama#84

// //////////////////////////////////////////////////////////////////////////////////////////////
// RUN
$app->run();
