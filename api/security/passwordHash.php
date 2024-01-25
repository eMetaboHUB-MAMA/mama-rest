<?php

/** 
 * Hash a password before storing it in database
 * @param String $password the password to hash
 * @return string the hashed password
 */
function create_hash($password)
{
	// note PHP 8 - salt option is ignored - using default SALT
	// encode
	$options = [
		'cost' => 12
	];
	return password_hash($password, PASSWORD_BCRYPT, $options);
}

/**
 * Generate a random string that can be used as temporary password when a user wants to reset it
 * @param Integer $length the length of the generated password
 */
function generateRandomPassword($length = 10)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomPassword = '';
	for ($i = 0; $i < $length; $i++) {
		$randomPassword .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomPassword;
}
