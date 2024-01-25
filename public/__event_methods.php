<?php
$app->get ( '/events', function ($request, $response, $args) {
	
	$projectID = null;
	if (isset ( $_GET ['projectID'] ) && $_GET ['projectID'] != "") {
		$projectID = $_GET ['projectID'];
	}
	
	// init response
	$data = [ ];
	
	if (isAdmin ()) {
		// ALL PROJECTS, and admin events
		$data = EventManagementService::getEvents ( null, $projectID, true );
	} else if (isProjectManager ()) {
		// ALL PROJECTS
		$data = EventManagementService::getEvents ( null, $projectID );
	} else if (TokenManagementService::isValide ( getToken () )) {
		// CURRENT USER PROJECTS
		$data = EventManagementService::getEvents ( TokenManagementService::getUserFromToken ( getToken () )->getId (), $projectID );
	} else {
		// return emtpty array
		$data = [ ];
	}
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"events" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} );

$app->get ( '/events-stats', function ($request, $response, $args) {
	
	// init response
	$data = Array ();
	
	if (TokenManagementService::isValide ( getToken () )) {
		$user = TokenManagementService::getUserFromToken ( getToken () );
		$data = Array (
				'userRight' => $user->getRight () 
		);
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

$app->get ( '/event[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	// TODO
} )->setArgument ( 'id', null );


// ////////////////////////////////////////////////////////////////////////////