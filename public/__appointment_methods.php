<?php
$app->get ( '/appointments', function ($request, $response, $args) {
	
	// init response
	$data = [ ];
	$data = getListOfAppointment ( $request, $response, $args );
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"appointments" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} );

$app->get ( '/appointments-count', function ($request, $response, $args) {
	
	$data = getListOfAppointment ( $request, $response, $args );
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"count" => sizeof ( $data ) 
		];
	}
	
	return formatResponse ( $request, $response, $args, sizeof ( $data ) );
} );
/**
 *
 * @param unknown $request        	
 * @param unknown $response        	
 * @param unknown $args        	
 * @return List
 */
function getListOfAppointment($request, $response, $args) {
	// init response
	$data = [ ];
	
	// $noUser = false;
	// if (isset ( $_GET ['noUsers'] ) && $_GET ['noUsers'] == "true") {
	// $noUser = true;
	// }
	
	$userId = intval ( TokenManagementService::getUserFromToken ( getToken () )->getId () );
	if (isAdmin () || isProjectManager ()) {
		// ALL PROJECTS
		$userFilter = null;
		$projectToFilter = null;
		if (isset ( $_GET ['userFilter'] ) && $_GET ['userFilter'] != "" && $_GET ['userFilter'] != "undefined") {
			$userFilter = $_GET ['userFilter'];
		}
		if (isset ( $_GET ['projectID'] ) && $_GET ['projectID'] != "" && $_GET ['projectID'] != "undefined") {
			$projectToFilter = ProjectManagementService::get ( intval ( $_GET ['projectID'] ) );
		}
		$dateFilter = null;
		if (isset ( $_GET ['filter'] ) && $_GET ['filter'] == "past") {
			$dateFilter = "past";
		} else if (isset ( $_GET ['filter'] ) && $_GET ['filter'] == "ongoing") {
			$dateFilter = "ongoing";
		}
		
		$data = AppointmentManagementService::getAppointments ( $userId, $userFilter, $projectToFilter, $dateFilter );
	} else if (TokenManagementService::isValide ( getToken () )) {
		// ALL PROJECTS
		$userFilter = null;
		$projectToFilter = null;
		if (isset ( $_GET ['projectID'] ) && $_GET ['projectID'] != "" && $_GET ['projectID'] != "undefined") {
			$projectToFilter = ProjectManagementService::get ( intval ( $_GET ['projectID'] ) );
			if ((TokenManagementService::getUserFromToken ( getToken () )->getId ()) != $projectToFilter->getOwner ()->getId ()) {
				return formatResponse401 ( $request, $response );
			}
		}
		$dateFilter = null;
		if (isset ( $_GET ['filter'] ) && $_GET ['filter'] == "past") {
			$dateFilter = "past";
		} else if (isset ( $_GET ['filter'] ) && $_GET ['filter'] == "ongoing") {
			$dateFilter = "ongoing";
		}
		$data = AppointmentManagementService::getAppointments ( $userId, $userFilter, $projectToFilter, $dateFilter );
		//
	} else {
		// return emtpty array
		$data = [ ];
	}
	return $data;
}

$app->get ( '/appointment[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	$data = [ ];
	
	$user = TokenManagementService::getUserFromToken ( getToken () );
	if (is_null ( $user ))
		return formatResponse401 ( $request, $response );
	
	$dataSec = AppointmentManagementService::get ( $id );
	if (isAdmin () || isProjectManager ()) {
		$data = $dataSec;
	} else {
		if ($dataSec->getToUser () != null && ($user->getId () == $dataSec->getToUser ()->getId ())) {
			$data = $dataSec;
		} else if ($dataSec->getFromUser () != null && ($user->getId () == $dataSec->getFromUser ()->getId ())) {
			$data = $dataSec;
		} else if ($dataSec->getToProject () != null && ($user->getId () == $dataSec->getToProject ()->getOwner ()->getId ())) {
			$data = $dataSec;
		} else {
			return formatResponse401 ( $request, $response );
		}
	}
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"appointment" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'id', null );

$app->post ( '/appointment', function ($request, $response, $args) {
	if (! (isset ( $_POST ['message'] ) && $_POST ['message'] != "")) {
		return formatResponse400 ( $request, $response );
	}
	
	$user = TokenManagementService::getUserFromToken ( getToken () );
	if (is_null ( $user )) {
		return formatResponse401 ( $request, $response );
	}
	
	// set tag whitelist
	$message = strip_tags ( $_POST ['message'], '<b><i><u><br>' );
	
	// set params
	$project = null;
	$fromUser = $user;
	$toUser = null;
	$appointmentDates = Array ();
	
	//
	if (isset ( $_POST ['userID'] ) && $_POST ['userID'] != "") {
		$toUser = UserManagementService::get ( intval ( $_POST ['userID'] ) );
	}
	if (isset ( $_POST ['projectID'] ) && $_POST ['projectID'] != "") {
		$project = ProjectManagementService::get ( intval ( $_POST ['projectID'] ) );
		// check is user ADMIN, PM or owner
		if (! (isAdmin () || isProjectManager () || $user->getId () == $project->getOwner ()->getId ())) {
			return formatResponse401 ( $request, $response );
		}
	}
	if (isset ( $_POST ['dates'] ) && $_POST ['dates'] != "") {
		foreach ( preg_split ( "/,/", $_POST ['dates'] ) as $key => $value ) {
			array_push ( $appointmentDates, $value );
		}
	}
	if (empty ( $appointmentDates )) {
		return formatResponse401 ( $request, $response );
	}
	
	// check dest
	if (is_null ( $toUser ) || is_null ( $project )) {
		return formatResponse401 ( $request, $response );
	}
	// return 201
	http_response_code ( 201 );
	$response = $response->withStatus ( 201 );
	
	// create appointment
	$data = AppointmentManagementService::create ( $message, $project, $fromUser, $toUser, $appointmentDates );
	
	// send email
	// SpecialEventMailler::sendEmailAppointmentCreate ( $dataSec ); // done in create static method
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"result" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} );

$app->delete ( '/appointment[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	// TODO
} )->setArgument ( 'id', null );

$app->put ( '/appointment[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	$isAdmin = false;
	$user = TokenManagementService::getUserFromToken ( getToken () );
	if (is_null ( $user ))
		return formatResponse401 ( $request, $response );
	
	$authorized = false;
	$dataSec = AppointmentManagementService::get ( $id );
	if (isAdmin () || isProjectManager ()) {
		$authorized = true;
		$isAdmin = true;
	} else {
		if ($dataSec->getToUser () != null && ($user->getId () == $dataSec->getToUser ()->getId ())) {
			$authorized = true;
		} else if ($dataSec->getFromUser () != null && ($user->getId () == $dataSec->getFromUser ()->getId ())) {
			$authorized = true;
		} else if ($dataSec->getProject () != null && ($user->getId () == $dataSec->getProject ()->getOwner ()->getId ())) {
			$authorized = true;
		} else {
			return formatResponse401 ( $request, $response );
		}
	}
	if ($authorized) {
		$dataSec = clone $dataSec;
	} else {
		return formatResponse401 ( $request, $response );
	}
	// update secure object
	$putData = $GLOBALS ['putData'];
	// var_dump ( $putData );
	
	$arrayAppProp = Array ();
	
	$nbSuccess = 0;
	$nbFail = 0;
	$appDate = null;
	
	// pending
	if (isset ( $putData ['pending'] ) && $putData ['pending'] != "") {
		foreach ( explode ( ",", $putData ['pending'] ) as $k => $v ) {
			$appT = AppointmentManagementService::getAppointmentProposition ( intval ( $v ) );
			$appT->setAppointmentSelected ( null );
			array_push ( $arrayAppProp, $appT );
		}
		// $dataSec->setAnalystsInvolved ( $arrayPending );
	}
	
	// accepted
	if (isset ( $putData ['accepted'] ) && $putData ['accepted'] != "") {
		foreach ( explode ( ",", $putData ['accepted'] ) as $k => $v ) {
			$appT = AppointmentManagementService::getAppointmentProposition ( intval ( $v ) );
			$appT->setAppointmentSelected ( true );
			array_push ( $arrayAppProp, $appT );
			$nbSuccess ++;
			$appDate = $appT->getAppointmentPropositionDate ();
		}
		// $dataSec->setAnalystsInvolved ( $arrayPending );
	}
	
	// rejected
	if (isset ( $putData ['rejected'] ) && $putData ['rejected'] != "") {
		foreach ( explode ( ",", $putData ['rejected'] ) as $k => $v ) {
			$appT = AppointmentManagementService::getAppointmentProposition ( intval ( $v ) );
			$appT->setAppointmentSelected ( false );
			array_push ( $arrayAppProp, $appT );
			$nbFail ++;
		}
		// $dataSec->setAnalystsInvolved ( $arrayPending );
	}
	
	$dataSec->setAppointmentDatesPropositions ( $arrayAppProp );
	
	// update
	$data = AppointmentManagementService::updateObject ( $dataSec, $isAdmin, $user );
	
	// send email
	$isSuccess = null;
	if ($nbSuccess == 1) {
		$isSuccess = true;
	} else if (sizeof ( $arrayAppProp ) == $nbFail) {
		$isSuccess = false;
	}
	
	SpecialEventMailler::sendEmailUpdateAppointment ( $dataSec, $dataSec->getProject (), $isSuccess, $appDate );
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"success" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'id', null );