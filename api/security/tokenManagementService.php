<?php
// //////////////////////////////////////////////////////////////////////////////////////////////
// SECURITY FUNCTIONS
require_once "../vendor/autoload.php";

require_once "../data-model/Token.class.php";
require_once "../data-model/User.class.php";

require_once "../api/security/passwordHash.php";
require_once "../api/services/userManagementService.php";

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
	$token = getToken ();
	
	if (TokenManagementService::isValide ( $token )) {
		return TokenManagementService::isAdmin ( $token );
	}
	
	// nope testa
	return false;
}
function isProjectManager() {
	$token = getToken ();
	
	if (TokenManagementService::isValide ( $token )) {
		return TokenManagementService::isProjectManager ( $token );
	}
	
	// nope testa
	return false;
}
function isTokenValide() {
	return TokenManagementService::isValide ( getToken () );
}

/**
 *
 * @param unknown $userID        	
 * @return boolean
 */
function isSameUser($userID) {
	if (is_null ( $userID ))
		return false;
	
	$token = getToken ();
	if (TokenManagementService::isValide ( $token )) {
		return TokenManagementService::isSameUserID ( $token, $userID );
	}
	
	// nope testa
	return false;
}
function isSameUserByLogin($userLogin) {
	if (is_null ( $userLogin ))
		return false;
	
	$token = getToken ();
	if (TokenManagementService::isValide ( $token )) {
		return TokenManagementService::isSameUserLogin ( $token, $userLogin );
	}
	
	// nope testa
	return false;
}

/**
 *
 * @param unknown $request        	
 * @param unknown $response        	
 */
function formatResponse401($request, $response) {
	http_response_code ( 401 );
	$response = $response->withStatus ( 401 );
	$data = new stdClass ();
	$data->success = false;
	$data->error = "unauthorized (require authentication and authorization)";
	
	// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"response" => $data 
		];
	}
	
	formatResponse ( $request, $response, null, $data );
}

/**
 *
 * @param unknown $request        	
 * @param unknown $response        	
 * @param unknown $cause        	
 */
function formatResponse400($request, $response, $cause = null) {
	http_response_code ( 400 );
	$response = $response->withStatus ( 400 );
	$data = new stdClass ();
	$data->success = false;
	$data->error = "Bad Request";
	if (! is_null ( $cause ))
		$data->cause = $cause;
		
		// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"response" => $data 
		];
	}
	
	formatResponse ( $request, $response, null, $data );
}

/**
 *
 * @param unknown $request        	
 * @param unknown $response        	
 * @param unknown $cause        	
 */
function formatResponse406($request, $response, $cause = null) {
	http_response_code ( 406 );
	$response = $response->withStatus ( 406 );
	$data = new stdClass ();
	$data->success = false;
	$data->error = "Not Acceptable";
	if (! is_null ( $cause ))
		$data->cause = $cause;
		
		// special case: XML lists
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/xml", $headerValueArray ) || getFormat () == "xml") {
		$data = [ 
				"response" => $data 
		];
	}
	
	formatResponse ( $request, $response, null, $data );
}

/**
 *
 * @author Nils Paulhe
 *        
 */
class TokenManagementService {
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CREATE
	public static function create($userLogin, $userPassword) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get user
		$user = UserManagementService::authenticate ( $userLogin, $userPassword );
		// bop
		if ($user == null || $user->isBlocked () || $user->isDeleted ())
			return null;
			// continue
		if ($user != null) {
			
			// get old one
			$token = TokenManagementService::getForUser ( $user->getId () );
			
			// delete if too old / renew otherwise
			if (! is_null ( $token )) {
				if ($token->getValidity () < (new \DateTime ( "now" ))) {
					// remove old one
					TokenManagementService::deleteForUser ( $user->getId () );
					$token = null;
				} else {
					// renew valididy
					TokenManagementService::renewValidity ( $token->getValue () );
				}
			}
			if (is_null ( $token )) {
				// create new
				$token = new Token ( $user );
				$entityManager->persist ( $token );
				$entityManager->flush ();
			}
			// set user last activity
			UserManagementService::setLastActivityNow ( $user );
			
			return $token;
		}
		
		return null;
	}
	public static function renewValidity($token) {
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get token
		$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.value = :token' );
		$query->setParameters ( array (
				'token' => $token 
		) );
		$tokens = $query->getResult ();
		if (count ( $tokens ) > 0) {
			$tokenToRenew = $tokens [0];
			$tokenToRenew->renewValidity ();
			$user = $tokenToRenew->getUser ();
			$entityManager->merge ( $tokenToRenew );
			$entityManager->flush ();
			UserManagementService::setLastActivityNow ( $user );
		}
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CHECK
	public static function isValide($token) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get token
		$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.value = :token' );
		$query->setParameters ( array (
				'token' => $token 
		) );
		$tokens = $query->getResult ();
		if (count ( $tokens ) > 0) {
			$tokenToCheck = $tokens [0];
			$user = $tokenToCheck->getUser ();
			if ($user->isBlocked () || $user->isDeleted ()) {
				TokenManagementService::delete ( $tokenToCheck->getValue () );
				return false;
			}
			if ($tokenToCheck->getValidity () < (new \DateTime ( "now" ))) {
				TokenManagementService::delete ( $tokenToCheck->getValue () );
				return false;
			}
			// IT IS OK!
			return true;
		}
		
		TokenManagementService::delete ( $token );
		return false;
	}
	
	/**
	 *
	 * @param unknown $token        	
	 * @return boolean
	 */
	public static function isAdmin($token) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get token
		$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.value = :token' );
		$query->setParameters ( array (
				'token' => $token 
		) );
		$tokens = $query->getResult ();
		if (count ( $tokens ) > 0) {
			$tokenToCheck = $tokens [0];
			$user = $tokenToCheck->getUser ();
			return $user->isAdmin ();
		}
		
		return false;
	}
	
	/**
	 *
	 * @param unknown $token        	
	 * @return boolean
	 */
	public static function isProjectManager($token) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get token
		$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.value = :token' );
		$query->setParameters ( array (
				'token' => $token 
		) );
		$tokens = $query->getResult ();
		if (count ( $tokens ) > 0) {
			$tokenToCheck = $tokens [0];
			$user = $tokenToCheck->getUser ();
			return $user->isProjectManager ();
		}
		
		return false;
	}
	
	/**
	 *
	 * @param unknown $token        	
	 * @param unknown $userID        	
	 * @return boolean
	 */
	public static function isSameUserID($token, $userID) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get token
		$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.value = :tokenV' );
		$query->setParameters ( array (
				'tokenV' => $token 
		) );
		$tokens = $query->getResult ();
		if (count ( $tokens ) > 0) {
			$tokenToCheck = $tokens [0];
			$user = $tokenToCheck->getUser ();
			return $user->getId () == $userID;
		}
		
		return false;
	}
	
	/**
	 *
	 * @param unknown $token        	
	 * @param unknown $userLogin        	
	 * @return boolean
	 */
	public static function isSameUserLogin($token, $userLogin) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get token
		$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.value = :tokenV' );
		$query->setParameters ( array (
				'tokenV' => $token 
		) );
		$tokens = $query->getResult ();
		if (count ( $tokens ) > 0) {
			$tokenToCheck = $tokens [0];
			$user = $tokenToCheck->getUser ();
			return $user->getLogin () == $userLogin;
		}
		
		return false;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// DELETE
	/**
	 */
	public static function clean($nbHours = null) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		if (is_null ( $nbHours )) {
			// delete all token decrepeted
			$tokens = $entityManager->getRepository ( 'Token' )->findAll ();
			foreach ( $tokens as $k => $token ) {
				TokenManagementService::isValide ( $token->getToken () );
			}
			return true;
		} else {
			// delete all token created before X houres
			$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.validity < :tokenDate' );
			$dateDel = (new \DateTime ( "now" ));
			$dateDel->add ( new DateInterval ( 'P10D' ) );
			$dateDel->sub ( new DateInterval ( 'PT' . $nbHours . 'H' ) );
			$query->setParameters ( array (
					'tokenDate' => $dateDel 
			) );
			$tokens = $query->getResult ();
			foreach ( $tokens as $k => $token ) {
				$tokenObj = $token;
				if ($tokenObj != null) {
					$entityManager->remove ( $tokenObj );
					$entityManager->flush ();
				}
			}
			return true;
		}
	}
	
	/**
	 *
	 * @param unknown $token        	
	 */
	public static function delete($token) {
		$entityManager = $GLOBALS ['entityManager'];
		$tokenObj = null;
		
		$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.value = :tokenV' );
		$query->setParameters ( array (
				'tokenV' => $token 
		) );
		$tokens = $query->getResult ();
		if (count ( $tokens ) > 0)
			$tokenObj = $tokens [0];
		
		if ($tokenObj != null) {
			$entityManager->remove ( $tokenObj );
			$entityManager->flush ();
			return true;
		}
		return false;
	}
	
	/**
	 *
	 * @param unknown $userID        	
	 */
	public static function deleteForUser($userID) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		$token = TokenManagementService::getForUser ( $userID );
		if ($token != null) {
			$entityManager->remove ( $token );
			$entityManager->flush ();
		}
	}
	public static function getForUser($userID) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.user = :userV' );
		$query->setParameters ( array (
				'userV' => UserManagementService::get ( $userID ) 
		) );
		$tokens = $query->getResult ();
		if (count ( $tokens ) > 0)
			return $tokens [0];
		else
			return null;
	}
	public static function getUserFromToken($token) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get token
		$query = $entityManager->createQuery ( 'SELECT t FROM Token t WHERE t.value = :token' );
		$query->setParameters ( array (
				'token' => $token 
		) );
		$tokens = $query->getResult ();
		if (count ( $tokens ) > 0) {
			$tokenToCheck = $tokens [0];
			$user = $tokenToCheck->getUser ();
			if ($user->isBlocked () || $user->isDeleted ()) {
				TokenManagementService::delete ( $tokenToCheck->getValue () );
				return null;
			}
			if ($tokenToCheck->getValidity () < (new \DateTime ( "now" ))) {
				TokenManagementService::delete ( $tokenToCheck->getValue () );
				return null;
			}
			// IT IS OK!
			return $user;
		}
		
		TokenManagementService::delete ( $token );
		return null;
	}
}
