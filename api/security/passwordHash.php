<?php
/**
 * @param String $password
 * @return string
 */
function create_hash($password) {
	$salt = null;
	
	// load cache in this method
	global $memcacheD;
	
	// check if salt in ram
	if ($memcacheD->get( "salt" ) ) {
		$salt = $memcacheD->get( "salt" );
	} else {
		// get salt
		$saltFile = __DIR__ . "/../../config/salt.txt";
		if (file_exists ( $saltFile )) {
			$fh = fopen ( $saltFile, 'r' );
			$salt = fgets ( $fh );
		} else {
			// create salt
			$salt = mcrypt_create_iv ( 22, MCRYPT_DEV_URANDOM );
			$fh = fopen ( $saltFile, 'w' );
			fwrite ( $fh, $salt );
			$memcacheD->set( "salt", $salt );
		}
		fclose ( $fh );
	}
	// encode
	$options = [ 
			'cost' => 12,
			'salt' => $salt 
	];
	return password_hash ( $password, PASSWORD_BCRYPT, $options );
}

/**
 *
 * @param String $password        	
 * @param String $correct_hash        	
 * @return boolean
 */
function validate_password($password, $correct_hash) {
	return (create_hash ( create_hash )) === ($correct_hash);
}
function generateRandomPassword($length = 6) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen ( $characters );
	$randomPassword = '';
	for($i = 0; $i < $length; $i ++) {
		$randomPassword .= $characters [rand ( 0, $charactersLength - 1 )];
	}
	return $randomPassword;
}
