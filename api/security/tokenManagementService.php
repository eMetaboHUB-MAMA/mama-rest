<?php
// //////////////////////////////////////////////////////////////////////////////////////////////
// SECURITY FUNCTIONS

/**
 *
 * @return string|unknown
 */
function getToken() {
	$token = "";
	// if (isset($_SESSION["token"]) && $_SESSION["token"]!=null){ $token = $_SESSION["token"]; }
	if (isset ( $_COOKIE ["token"] ) && $_COOKIE ["token"] != null) {
		$token = $_COOKIE ["token"];
	}
	if (isset ( $_POST ["token"] ) && $_POST ["token"] != null) {
		$token = $_POST ["token"];
	}
	if (isset ( $_GET ["token"] ) && $_GET ["token"] != null) {
		$token = $_GET ["token"];
	}
	return $token;
}

/**
 *
 * @return boolean
 */
function isAdmin() {
	// TODO get user in DB
	if (getToken () == $GLOBALS ['_tokenAdmin']) {
		return true;
	}
	
	// nope testa
	return false;
}

/**
 * @param unknown $request
 * @param unknown $response
 */
function formatResponse401($request, $response) {
	http_response_code ( 401 );
	$response = $response->withStatus ( 401 );
	$data = new stdClass ();
	$data->success = false;
	$data->error = "unauthorized";
	formatResponse ( $request, $response, null, $data );
}