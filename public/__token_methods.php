<?php
$app->post ( '/token', function ($request, $response, $args) {
	
	// return 201
	http_response_code ( 201 );
	$response = $response->withStatus ( 201 );
	
	// results
	$data = null;
	
	// login via email
	if (! isset ( $_POST ['login'] ) || $_POST ['login'] == "" || ! isset ( $_POST ['password'] ) || $_POST ['password'] == "") {
		return formatResponse400 ( $request, $response, "missing mandatory parameters (login|password)." );
	}
	
	$userLogin = $_POST ['login'];
	$userPassword = $_POST ['password'];
	
	// special case: ldap user, check if exist, otherwise create them
	if (! strpos ( $userLogin, '@' )) { // || preg_match ( apc_fetch ( "ldap_email_filter" ), $userLogin, $matches )
		$ldapLogin = $userLogin;
		// if (isset ( $matches ) && count ( $matches ) > 0) {
		// $ldapLogin = $matches [1];
		// }
		// create user if does not exists
		$isNewUser = UserManagementService::tryInitLDAP ( $ldapLogin, $userPassword );
		if ($isNewUser) {
			$newUser = UserManagementService::getByLogin ( $ldapLogin );
			SpecialEventMailler::sendEmailNewUser ( $ldapLogin, $newUser->getEmail () );
		}
	}
	
	$data = TokenManagementService::create ( $userLogin, $userPassword );
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"result" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} );

$app->delete ( '/token[/{token}]', function ($request, $response, $args) {
	
	$token = ($args ['token']);
	$data = TokenManagementService::delete ( $token );
	if ($data) {
		// unset($_SESSION['token']);
		// $_SESSION['token'] = null;
		unset ( $_COOKIE ['token'] );
		setcookie ( 'token', '', time () - 3600 );
	}
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"result" => $data 
		];
	}
	
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'token', null );