<?php
$app->get ( '/users', function ($request, $response, $args) {
	
	// init response
	$data = [ ];
	
	if (isAdmin ()) {
		$data = UserManagementService::getUsers ();
	} else {
		return formatResponse401 ( $request, $response );
	}
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"users" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} );

$app->get ( '/projects-managers', function ($request, $response, $args) {
	
	// init response
	$data = [ ];
	
	if (isProjectManager () || isAdmin ()) {
		$data = UserManagementService::getProjectsManagers ();
	} else {
		return formatResponse401 ( $request, $response );
	}
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"users" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data, true );
} );

$app->get ( '/users-stats', function ($request, $response, $args) {
	
	// init response
	$data = Array ();
	
	if (isAdmin ()) {
		$data ['usersAdmin'] = UserManagementService::countUsers ( "admin" );
		$data ['usersProjectManager'] = UserManagementService::countUsers ( "project_manager" );
		$data ['usersUsers'] = UserManagementService::countUsers ( "user" );
		$data ['usersBlocked'] = UserManagementService::countUsers ( "blocked" );
		$data ['usersInactive'] = UserManagementService::countUsers ( "inactive" );
		$data ['usersNotActivated'] = UserManagementService::countUsers ( "not_validated" );
	} else {
		return formatResponse401 ( $request, $response );
	}
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"statistics" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} );

$app->get ( '/user[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	$login = null;
	
	if (isset ( $_GET ['login'] ) && $_GET ['login'] != "")
		$login = $_GET ['login'];
		
		// init response
	$data = [ ];
	
	if (isAdmin () || isSameUserByLogin ( $login ) || isSameUser ( $id )) { //
		if ($login != null)
			$data = UserManagementService::getByLogin ( $login );
		else
			$data = UserManagementService::get ( $id );
	} else {
		return formatResponse401 ( $request, $response );
	}
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"user" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'id', null );

$app->post ( '/user', function ($request, $response, $args) {
	if (! isset ( $_POST ['email'] ) || $_POST ['email'] == "" || ! isset ( $_POST ['password'] ) || $_POST ['password'] == "") {
		return formatResponse400 ( $request, $response );
	}
	
	$login = $_POST ['email'];
	$statusRight = $_POST ['password'];
	
	(isset ( $_POST ["firstName"] ) && $_POST ['firstName'] != "") ? $firstName = $_POST ["firstName"] : $firstName = null;
	(isset ( $_POST ["lastName"] ) && $_POST ['lastName'] != "") ? $lastName = $_POST ["lastName"] : $lastName = null;
	(isset ( $_POST ["phoneGroup"] ) && $_POST ['phoneGroup'] != "") ? $phoneGroup = $_POST ["phoneGroup"] : $phoneGroup = null;
	(isset ( $_POST ["phoneNumber"] ) && $_POST ['phoneNumber'] != "") ? $phoneNumber = $_POST ["phoneNumber"] : $phoneNumber = null;
	(isset ( $_POST ["laboratoryOrCompagny"] ) && $_POST ['laboratoryOrCompagny'] != "") ? $laboratoryOrCompagny = $_POST ["laboratoryOrCompagny"] : $laboratoryOrCompagny = null;
	(isset ( $_POST ["workplaceAddress"] ) && $_POST ['workplaceAddress'] != "") ? $workplaceAddress = $_POST ["workplaceAddress"] : $workplaceAddress = null;
	(isset ( $_POST ["typeOfLaboratoryOrCompany"] ) && $_POST ['typeOfLaboratoryOrCompany'] != "") ? $typeOfLaboratoryOrCompany = $_POST ["typeOfLaboratoryOrCompany"] : $typeOfLaboratoryOrCompany = null;
	$lang = "en";
	if (isset ( $_GET ["lang"] ) && $_GET ['lang'] != "") {
		if (strtolower ( $_GET ['lang'] ) == "fr") {
			$lang = "fr";
		}
	}
	
	if (UserManagementService::exists ( $login )) {
		return formatResponse406 ( $request, $response, "a user with this email already exists" );
	}
	// return 201
	http_response_code ( 201 );
	$response = $response->withStatus ( 201 );
	
	$data = UserManagementService::create ( $login, $login, $statusRight, $firstName, $lastName, $phoneGroup, $phoneNumber, $laboratoryOrCompagny, $workplaceAddress, $typeOfLaboratoryOrCompany );
	EmailManagementService::sendEmailAccountCreation ( $login, $firstName . " " . $lastName, $lang );
	SpecialEventMailler::sendEmailNewUser ( $login, $login );
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"result" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} );

$app->put ( '/user[/{id}]', function ($request, $response, $args) {
	
	$id = intval ( $args ['id'] );
	if (! (isAdmin () || isSameUser ( $id ))) {
		return formatResponse401 ( $request, $response );
	}
	
	$currentUser = TokenManagementService::getUserFromToken ( getToken () );
	
	$putData = $GLOBALS ['putData'];
	
	(isset ( $putData ["password"] ) && $putData ['password'] != "") ? $statusRight = $putData ["password"] : $statusRight = null;
	(isset ( $putData ["old-password"] ) && $putData ['old-password'] != "") ? $passwordOld = $putData ["old-password"] : $passwordOld = null;
	
	$data = true;
	if ($statusRight != null && $passwordOld != null) {
		$data = UserManagementService::updatePasswordCheck ( $id, $passwordOld, $statusRight, $currentUser );
		$statusRight = null;
	}
	
	(isset ( $putData ["firstName"] ) && $putData ['firstName'] != "") ? $firstName = $putData ["firstName"] : $firstName = null;
	(isset ( $putData ["lastName"] ) && $putData ['lastName'] != "") ? $lastName = $putData ["lastName"] : $lastName = null;
	(isset ( $putData ["phoneGroup"] ) && $putData ['phoneGroup'] != "") ? $phoneGroup = intval ( $putData ["phoneGroup"] ) : $phoneGroup = null;
	(isset ( $putData ["phoneNumber"] ) && $putData ['phoneNumber'] != "") ? $phoneNumber = $putData ["phoneNumber"] : $phoneNumber = null;
	(isset ( $putData ["laboratoryOrCompagny"] ) && $putData ['laboratoryOrCompagny'] != "") ? $laboratoryOrCompagny = $putData ["laboratoryOrCompagny"] : $laboratoryOrCompagny = null;
	(isset ( $putData ["workplaceAddress"] ) && $putData ['workplaceAddress'] != "") ? $workplaceAddress = $putData ["workplaceAddress"] : $workplaceAddress = null;
	(isset ( $putData ["laboratoryType"] ) && $putData ['laboratoryType'] != "") ? $typeOfLaboratoryOrCompany = $putData ["laboratoryType"] : $typeOfLaboratoryOrCompany = null;
	
	(isset ( $putData ["emailNotification"] ) && $putData ['emailNotification'] != "") ? $emailReception = $putData ["emailNotification"] : $emailReception = null;
	
	(isset ( $putData ["emailAlertNewUserAccount"] ) && $putData ['emailAlertNewUserAccount'] != "") ? $emailAlertNewUserAccount = $putData ["emailAlertNewUserAccount"] : $emailAlertNewUserAccount = false;
	(isset ( $putData ["emailAlertNewProject"] ) && $putData ['emailAlertNewProject'] != "") ? $emailAlertNewProject = $putData ["emailAlertNewProject"] : $emailAlertNewProject = false;
	(isset ( $putData ["emailAlertNewEventFollowedProject"] ) && $putData ['emailAlertNewEventFollowedProject'] != "") ? $emailAlertNewEventFollowedProject = $putData ["emailAlertNewEventFollowedProject"] : $emailAlertNewEventFollowedProject = false;
	(isset ( $putData ["emailAlertNewMessage"] ) && $putData ['emailAlertNewMessage'] != "") ? $emailAlertNewMessage = $putData ["emailAlertNewMessage"] : $emailAlertNewMessage = false;
	
	$emailAlertNewUserAccount = ($emailAlertNewUserAccount === 'true' ? true : false);
	$emailAlertNewProject = ($emailAlertNewProject === 'true' ? true : false);
	$emailAlertNewEventFollowedProject = ($emailAlertNewEventFollowedProject === 'true' ? true : false);
	$emailAlertNewMessage = ($emailAlertNewMessage === 'true' ? true : false);
	(isset ( $putData ["emailLanguage"] ) && $putData ['emailLanguage'] != "") ? $emailLanguage = $putData ["emailLanguage"] : $emailLanguage = "en";
	
	if ($data)
		$data = UserManagementService::update ( $id, null, $firstName, $lastName, $phoneGroup, $phoneNumber, $laboratoryOrCompagny, $workplaceAddress, $typeOfLaboratoryOrCompany, $emailReception, $emailAlertNewUserAccount, $emailAlertNewProject, $emailAlertNewEventFollowedProject, $emailAlertNewMessage, $emailLanguage, $currentUser );
		
		// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"success" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'id', null );

$app->put ( '/reset-password', function ($request, $response, $args) {
	
	$currentUser = TokenManagementService::getUserFromToken ( getToken () );
	
	$putData = $GLOBALS ['putData'];
	
	(isset ( $putData ["login"] ) && $putData ['login'] != "") ? $login = $putData ["login"] : $login = null;
	
	$lang = "en";
	if (isset ( $_GET ["lang"] ) && $_GET ['lang'] != "") {
		if (strtolower ( $_GET ['lang'] ) == "fr") {
			$lang = "fr";
		}
	}
	
	$data = false;
	if ($login != null && $login != null) {
		$passwordNew = generateRandomPassword ();
		$data = UserManagementService::resetPassword ( $login, $passwordNew, $currentUser );
	}
	
	if ($data) {
		// send email
		$data = EmailManagementService::sendEmailResetPassword ( $login, $passwordNew, $lang );
	}
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"success" => $data 
		];
	}
	
	// send response
	return formatResponse ( $request, $response, $args, $data );
} );

$app->put ( '/user-right[/{id}]', function ($request, $response, $args) {
	
	$id = intval ( $args ['id'] );
	if (! isAdmin ()) {
		return formatResponse401 ( $request, $response );
	}
	
	$currentUser = TokenManagementService::getUserFromToken ( getToken () );
	
	$putData = $GLOBALS ['putData'];
	
	(isset ( $putData ["statusRight"] ) && $putData ['statusRight'] != "") ? $statusRight = $putData ["statusRight"] : $statusRight = null;
	
	$data = UserManagementService::updateRight ( $id, $statusRight, $currentUser );
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"success" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'id', null );

$app->delete ( '/user[/{id}]', function ($request, $response, $args) {
	// TODO only admin / current user
} )->setArgument ( 'id', null );