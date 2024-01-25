<?php
// ////////////////////////////////////////////////////////////////////////////
$app->get ( '/server-load', function ($request, $response, $args) {
	// init response
	$data = [ ];
	if (isAdmin ()) {
		// RAM
		$free = shell_exec ( 'free' );
		$free = ( string ) trim ( $free );
		$free_arr = explode ( "\n", $free );
		$mem = explode ( " ", $free_arr [1] );
		$mem = array_filter ( $mem );
		$mem = array_merge ( $mem );
		$memory_usage = ($mem [2] / $mem [1] * 100);
		// CPU
		$load = sys_getloadavg ();
		$data = array (
				"cpu-1-min" => $load [0],
				"cpu-5-min" => $load [1],
				"cpu-15-min" => $load [2],
				"ram" => $memory_usage 
		);
	} else {
		// return 401
		return formatResponse401 ( $request, $response );
	}
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"load" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} );
// ////////////////////////////////////////////////////////////////////////////

$app->get ( '/keywords', function ($request, $response, $args) {
	// init response
	$data = KeywordManagementService::getKeywords ();
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"keywords" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} );

$app->get ( '/subkeywords', function ($request, $response, $args) {
	// init response
	$data = KeywordManagementService::getSubKeywords ();
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"subkeywords" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} );

$app->post ( '/keyword', function ($request, $response, $args) {
	if (! isAdmin ()) {
		return formatResponse401 ( $request, $response );
	}
	$user = TokenManagementService::getUserFromToken ( getToken () );
	$mthPlatform = $_POST ['keyword'];
	$data = KeywordManagementService::create ( $mthPlatform, $user );
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"result" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} );

$app->post ( '/subkeyword', function ($request, $response, $args) {
	if (! isAdmin ()) {
		return formatResponse401 ( $request, $response );
	}
	$user = TokenManagementService::getUserFromToken ( getToken () );
	$mthPlatform = $_POST ['keyword'];
	$data = KeywordManagementService::createSub ( $mthPlatform, $user );
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"result" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} );

$app->put ( '/keyword[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	if (! isAdmin ()) {
		return formatResponse401 ( $request, $response );
	}
	$putData = $GLOBALS ['putData'];
	$user = TokenManagementService::getUserFromToken ( getToken () );
	(isset ( $putData ["keyword"] ) && $putData ['keyword'] != "") ? $mthPlatform = $putData ['keyword'] : $mthPlatform = null;
	$deleted = null;
	if (isset ( $putData ["deleted"] ) && $putData ["deleted"] != "") {
		$mthPlatform = null;
		($putData ['deleted'] == "true") ? $deleted = true : $deleted = false;
	}
	$data = KeywordManagementService::update ( $id, $mthPlatform, $deleted, $user );
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"success" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'id', null );

$app->put ( '/subkeyword[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	if (! isAdmin ()) {
		return formatResponse401 ( $request, $response );
	}
	$putData = $GLOBALS ['putData'];
	$user = TokenManagementService::getUserFromToken ( getToken () );
	(isset ( $putData ["keyword"] ) && $putData ['keyword'] != "") ? $mthPlatform = $putData ['keyword'] : $mthPlatform = null;
	$deleted = null;
	if (isset ( $putData ["deleted"] ) && $putData ["deleted"] != "") {
		$mthPlatform = null;
		($putData ['deleted'] == "true") ? $deleted = true : $deleted = false;
	}
	$data = KeywordManagementService::updateSub ( $id, $mthPlatform, $deleted, $user );
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"success" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'id', null );

// $app->delete ( '/keyword[/{id}]', function ($request, $response, $args) {

// $id = intval ( $args ['id'] );
// if (! isAdmin ()) {
// return formatResponse401 ( $request, $response );
// }
// // TODO $data = ...
// // special case: XML lists
// $headerValueArray = $request->getHeader ( 'Accept' );
// if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
// $data = [
// "success" => $data
// ];
// }
// return formatResponse ( $request, $response, $args, $data );
// } )->setArgument ( 'id', null );

// ////////////////////////////////////////////////////////////////////////////

$app->get ( '/mth-platforms', function ($request, $response, $args) {
	// init response
	$data = MTHPlatformManagementService::getMTHPlatforms ();
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"mth-platoforms" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} );

$app->post ( '/mth-platform', function ($request, $response, $args) {
	if (! isAdmin ()) {
		return formatResponse401 ( $request, $response );
	}
	$user = TokenManagementService::getUserFromToken ( getToken () );
	$mthPlatform = $_POST ['mthPlatform'];
	$data = MTHPlatformManagementService::create ( $mthPlatform, $user );
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"result" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} );

$app->put ( '/mth-platform[/{id}]', function ($request, $response, $args) {
	$id = intval ( $args ['id'] );
	if (! isAdmin ()) {
		return formatResponse401 ( $request, $response );
	}
	$putData = $GLOBALS ['putData'];
	$user = TokenManagementService::getUserFromToken ( getToken () );
	(isset ( $putData ["mthPlatform"] ) && $putData ['mthPlatform'] != "") ? $mthPlatform = $putData ['mthPlatform'] : $mthPlatform = null;
	
	$data = MTHPlatformManagementService::update ( $id, $mthPlatform, $user );
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"success" => $data 
		];
	}
	return formatResponse ( $request, $response, $args, $data );
} )->setArgument ( 'id', null );
