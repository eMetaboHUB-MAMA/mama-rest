<?php
require_once '../api/utils/formatStatistics.php';

$app->get ( '/statistics', function ($request, $response, $args) {
	// init response
	$data = [ ];
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (isAdmin ()) {
		if (in_array ( "application/xls", $headerValueArray ) || getFormat () == "xls") {
			return getXLSstatisticsFile ();
		}
	} else {
		return formatResponse401 ( $request, $response );
	}
	
} );

$app->get ( '/projects-statistics', function ($request, $response, $args) {
	
	// init response
	$data = [ ];
	$headerValueArray = $request->getHeader ( 'Accept' );
	
	if (isAdmin ()) {
		$data = StatisticManagementService::getProjectsStats ();
	} else {
		// return emtpty array
		$data = [ ];
	}
	
	// special case: XML lists
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"projects-statistics" => $data 
		];
	}
	
	// json / txt/ ...
	return formatResponse ( $request, $response, $args, $data );
} );

$app->get ( '/users-statistics', function ($request, $response, $args) {
	
	// init response
	$data = [ ];
	$headerValueArray = $request->getHeader ( 'Accept' );
	
	if (isAdmin ()) {
		$data = StatisticManagementService::getUsersStats ();
	} else {
		// return emtpty array
		$data = [ ];
	}
	
	// special case: XML lists
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"users-statistics" => $data 
		];
	}
	
	// json / txt/ ...
	return formatResponse ( $request, $response, $args, $data );
} );

$app->get ( '/extra-data-statistics', function ($request, $response, $args) {
	
	// init response
	$data = [ ];
	$headerValueArray = $request->getHeader ( 'Accept' );
	
	if (isAdmin ()) {
		$data = StatisticManagementService::getExtraDataStats ();
	} else {
		// return emtpty array
		$data = [ ];
	}
	
	// special case: XML lists
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"extra-data-statistics" => $data 
		];
	}
	
	// json / txt/ ...
	return formatResponse ( $request, $response, $args, $data );
} );
