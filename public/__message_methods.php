<?php
$app->get ( '/messages', function ($request, $response, $args) {
	
	// init response
	$data = [ ];
	$data = getListOfMessage ( $request, $response, $args );
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"messages" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} );

$app->get ( '/messages-count', function ($request, $response, $args) {
	
	$data = getListOfMessage ( $request, $response, $args );
	
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
function getListOfMessage($request, $response, $args) {
	// init response
	$data = [ ];
	
	$noUser = false;
	if (isset ( $_GET ['noUsers'] ) && $_GET ['noUsers'] == "true") {
		$noUser = true;
	}
	
	if (isAdmin () || isProjectManager ()) {
		// ALL PROJECTS
		$userToFilter = null;
		$userFilter = null;
		$projectToFilter = null;
		$projectFilter = null;
		$user2project = TokenManagementService::getUserFromToken ( getToken () )->getId ();
		$user2projectFilter = "all";
		if (isset ( $_GET ['userFilter'] ) && $_GET ['userFilter'] != "" && $_GET ['userFilter'] != "undefined") {
			$userToFilter = TokenManagementService::getUserFromToken ( getToken () )->getId ();
			$userFilter = $_GET ['userFilter'];
		} else {
			$userToFilter = TokenManagementService::getUserFromToken ( getToken () )->getId ();
			// $userFilter = "receiver";
			$userFilter = "all";
		}
		if (isset ( $_GET ['projectID'] ) && $_GET ['projectID'] != "" && $_GET ['projectID'] != "undefined") {
			$projectToFilter = ProjectManagementService::get ( intval ( $_GET ['projectID'] ) );
		}
		if (isset ( $_GET ['projectFilter'] ) && $_GET ['projectFilter'] != "" && $_GET ['projectFilter'] != "undefined") {
			$projectFilter = $_GET ['projectFilter'];
		}
		if (isset ( $_GET ['projectPlaceFilter'] ) && $_GET ['projectPlaceFilter'] != "" && $_GET ['projectPlaceFilter'] != "undefined") {
			// $user2project = TokenManagementService::getUserFromToken ( getToken () )->getId ();
			$user2projectFilter = $_GET ['projectPlaceFilter'];
		}
		$data = MessageManagementService::getMessages ( $userToFilter, $userFilter, $projectToFilter, $projectFilter, $user2project, $user2projectFilter, $noUser );
	} else if (TokenManagementService::isValide ( getToken () )) {
		// ALL PROJECTS
		$userToFilter = null;
		$userFilter = null;
		$projectToFilter = null;
		$projectFilter = null;
		$user2project = TokenManagementService::getUserFromToken ( getToken () )->getId ();
		$user2projectFilter = "owner";
		// CURRENT USER PROJECTS
		if (isset ( $_GET ['userFilter'] ) && $_GET ['userFilter'] == "sender") {
			$userFilter = "sender";
		} else {
			// default filter: user message receiver
			// $userFilter = "receiver";
			$userFilter = "all";
		}
		// default filter: user project owner
		if (isset ( $_GET ['projectID'] ) && $_GET ['projectID'] != "" && $_GET ['projectID'] != "undefined") {
			$projectToFilter = ProjectManagementService::get ( intval ( $_GET ['projectID'] ) );
			if ((TokenManagementService::getUserFromToken ( getToken () )->getId ()) != $projectToFilter->getOwner ()->getId ()) {
				return formatResponse401 ( $request, $response );
			}
		}
		if (isset ( $_GET ['projectFilter'] ) && $_GET ['projectFilter'] != "" && $_GET ['projectFilter'] != "undefined") {
			$projectFilter = $_GET ['projectFilter'];
		}
		if (isset ( $_GET ['projectPlaceFilter'] ) && $_GET ['projectPlaceFilter'] != "" && $_GET ['projectPlaceFilter'] != "undefined") {
			// $user2project = TokenManagementService::getUserFromToken ( getToken () )->getId ();
			$user2projectFilter = $_GET ['projectPlaceFilter'];
		}
		$data = MessageManagementService::getMessages ( TokenManagementService::getUserFromToken ( getToken () )->getId (), $userFilter, $projectToFilter, $projectFilter, $user2project, $user2projectFilter, $noUser );
		//
	} else {
		// return emtpty array
		$data = [ ];
	}
	return $data;
}

$app->get ( '/message[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	$data = [ ];
	
	$user = TokenManagementService::getUserFromToken ( getToken () );
	if (is_null ( $user ))
		return formatResponse401 ( $request, $response );
	
	$dataSec = MessageManagementService::get ( $id );
	if (isAdmin () || isProjectManager ()) {
		$data = $dataSec;
	} else {
		if ($dataSec->getToUser () != null && ($user->getId () == $dataSec->getToUser ()->getId ())){
			$data = $dataSec;
		} else if ($dataSec->getFromUser () != null && ($user->getId () == $dataSec->getFromUser()->getId ())){
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
				"message" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'id', null );

$app->post ( '/message', function ($request, $response, $args) {
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
	$fromUser = $user;
	$fromProject = null;
	$toUser = null;
	$toProject = null;
	
	//
	if (isset ( $_POST ['userID'] ) && $_POST ['userID'] != "") {
		$toUser = UserManagementService::get ( intval ( $_POST ['userID'] ) );
	}
	if (isset ( $_POST ['projectID'] ) && $_POST ['projectID'] != "") {
		$toProject = ProjectManagementService::get ( intval ( $_POST ['projectID'] ) );
		$toUser = null;
		// check is user ADMIN, PM or owner
		if (! (isAdmin () || isProjectManager () || $user->getId () == $toProject->getOwner ()->getId ())) {
			return formatResponse401 ( $request, $response );
		}
	}
	
	// check dest
	if (is_null ( $toUser ) && is_null ( $toProject )) {
		return formatResponse401 ( $request, $response );
	}
	// return 201
	http_response_code ( 201 );
	$response = $response->withStatus ( 201 );
	
	$data = MessageManagementService::create ( $message, $fromUser, $fromProject, $toUser, $toProject );
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"result" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} );

$app->delete ( '/message[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	// TODO
} )->setArgument ( 'id', null );

