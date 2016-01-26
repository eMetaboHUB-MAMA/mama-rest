<?php

/**
 * @return stdClass
 */
function getMamaInfos() {
	$data = new stdClass ();
	$data->message = 'Welcome to MAMA-REST!';
	$data->documentation = 'https://mama-doc.metabohub.fr';
	$st = 1381309000; // a timestamp
	$dt = new DateTime ( "@$st" );
	// TODO add git sha1
	$data->informations = [ 
			"version" => "0.0",
			"release" => $dt 
	];
	return $data;
}