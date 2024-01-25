<?php

/**
 * @return stdClass
 */
function getMamaInfos() {
	$mama = new stdClass ();
	$data = new stdClass ();
	
	$application_message = "?";
	$application_documentation = "?";
	$application_name = "?";
	$application_version = "0.0";
	
	// data
	$configFile = __DIR__ . "/../../config/mama-config.ini";
	if (file_exists ( $configFile )) {
		$ini_array = parse_ini_file ( $configFile, true );
		$application_message = $ini_array ['application'] ['message'];
		;
		$application_documentation = $ini_array ['application'] ['documentation'];
		;
		$application_name = $ini_array ['application'] ['name'];
		$application_version = $ini_array ['application'] ['version'];
	}
	
	// basic infos
	$data->message = $application_message;
	$data->documentation = $application_documentation;
	
	// sha1 and timestamp
	$st = getGitTimestamp ();
	$sha1 = getGitSHA1 ();
	$dt = new DateTime ( "@$st" );
	$data->api = [ 
			"name" => $application_name,
			"version" => $application_version,
			"commit" => $sha1,
			"releaseDate" => $dt 
	];
	
	// return
	$mama->mama = [ 
			$data 
	];
	return $mama;
}

/**
 * Get last commit data (light)
 */
function getGitData() {
	return trim ( `git log -1 --pretty=format:'%h - %s (%ci)' --abbrev-commit` );
}

/**
 * Get last commit SHA1
 */
function getGitSHA1() {
	return trim ( `git rev-parse HEAD` );
}

/**
 * Get last commit SHA1
 */
function getGitTimestamp() {
	return trim ( `git show -s --format=%ct` );
}