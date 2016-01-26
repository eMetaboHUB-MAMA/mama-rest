<?php

// session_start();

// test sandbox
$_tokenAdmin = "adminToken";
$_tokenCurrentUser = "currentUserToken";

// API
require '../vendor/autoload.php';

// UTILS
require_once '../api/utils/format.php';
require_once '../api/security/tokenManagementService.php';

// MAMA OBJECTS
require_once "../bootstrap.php";
require_once '../api/services/mamaManagementService.php';
require_once '../api/services/userManagementService.php';

$app = new Slim\App ();

// //////////////////////////////////////////////////////////////////////////////////////////////
// INFOS
$app->get ( '/', function ($request, $response, $args) {
	
	// init response
	$data = getMamaInfos ();
	
	return formatResponse ( $request, $response, $args, $data );
} );

// //////////////////////////////////////////////////////////////////////////////////////////////
// TOKEN
$app->post ( '/token', function ($request, $response, $args) {
	
	// TODO assoc uniq token to user in db
} );

$app->delete ( '/token', function ($request, $response, $args) {
	
	// unset($_SESSION['token']);
	// $_SESSION['token'] = null;
	
	unset ( $_COOKIE ['token'] );
	setcookie ( 'token', '', time () - 3600 );
	
	// TODO delete in DB
} );

// //////////////////////////////////////////////////////////////////////////////////////////////
// USERS

$app->get ( '/users', function ($request, $response, $args) {
	
	// init response
	$data = [ ];
	
	if (isAdmin ()) {
		$data = getUsers ();
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

$app->get ( '/user[/{id}]', function ($request, $response, $args) {
	// TODO only admin / current user
} )->setArgument ( 'id', null );

$app->post ( '/user', function ($request, $response, $args) {
	// TODO only new user; return 201
} );

$app->put ( '/user[/{id}]', function ($request, $response, $args) {
	// TODO only admin / current user
} )->setArgument ( 'id', null );

$app->delete ( '/user[/{id}]', function ($request, $response, $args) {
	// TODO only admin / current user
} )->setArgument ( 'id', null );

// //////////////////////////////////////////////////////////////////////////////////////////////
// HELLO
// $app->get('/hello[/{name}]', function ($request, $response, $args) {
// $response->write("Hello, " . $args['name']);
// return $response;
// })->setArgument('name', 'World!');

// //////////////////////////////////////////////////////////////////////////////////////////////
// RUN
$app->run ();

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA USER FUNCTIONS


// --- sandbox!!!