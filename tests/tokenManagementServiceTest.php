<?php
require_once "bootstrap.tests.php";
require_once "../api/security/tokenManagementService.php";
require_once '../api/services/eventManagementService.php';
// require_once "../data-model/User.class.php";
// require_once "../vendor/autoload.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class TokenManagementServiceTest extends PHPUnit\Framework\TestCase {
	
	/**
	 */
	public function testCreaded() {
		
		// create user
		$email = "junit.test.createToken" . time () . "@inra.fr";
		$password = "pouic meuh pouic meuh pouic";
		$id = UserManagementService::create ( $email, $email, $password, null, null, null, null, null, null, null );
		// $user = UserManagementService::get($id);
		
		// create token
		$token = TokenManagementService::create ( $email, $password );
		
		// check 1
		$this->assertEquals ( false, is_null ( $token ), "[error] 'create' does not work" );
		
		// check 2
		$tokenObj = TokenManagementService::getForUser ( $id );
		$this->assertEquals ( $token->getValue (), $tokenObj->getValue (), "[error] 'create' or 'getForUser' (by id) do not work" );
		
		// create new token
		$tokenNew = TokenManagementService::create ( $email, $password );
		$tokenObj2 = TokenManagementService::getForUser ( $id );
		
		// check if old is deleted
		$this->assertEquals ( $tokenNew->getValue (), $tokenObj2->getValue (), "[error] 'create' or 'getForUser' (by id) do not work" );
		$this->assertEquals ( true, TokenManagementService::isValide ( $token->getValue () ), "[error] 'create' or 'getForUser' (by id) do not work" );
		$this->assertEquals ( true, TokenManagementService::isValide ( $tokenNew->getValue () ), "[error] 'create' or 'getForUser' (by id) do not work" );
	}
	
	/**
	 */
	public function testIsSame() {
		
		// create user
		$email = "junit.test.checkToken" . time () . "@inra.fr";
		$password = "pouic meuh pouic meuh pouic";
		$id = UserManagementService::create ( $email, $email, $password, null, null, null, null, null, null, null, "validated", "admin" );
		
		// create token
		$token = TokenManagementService::create ( $email, $password );
		$tokenVal = $token->getValue ();
		
		// check admin
		$this->assertEquals ( false, TokenManagementService::isAdmin ( "pouicpouic" ), "[error] checking admin status of token" );
		$this->assertEquals ( true, TokenManagementService::isAdmin ( $tokenVal ), "[error] checking admin status of token" );
		
		// check same user by id
		$this->assertEquals ( false, TokenManagementService::isSameUserID ( "pouicpouic", $id ), "[error] checking admin status of token" );
		$this->assertEquals ( true, TokenManagementService::isSameUserID ( $tokenVal, $id ), "[error] checking admin status of token" );
		
		// check same user by email
		$this->assertEquals ( false, TokenManagementService::isSameUserLogin ( "pouicpouic", $email ), "[error] checking admin status of token" );
		$this->assertEquals ( true, TokenManagementService::isSameUserLogin ( $tokenVal, $email ), "[error] checking admin status of token" );
	}
	
// 	/**
// 	 */
// 	public function testDelete() {
// 		// // create
// 		// $email = "junit.test.delete" . time () . "@inra.fr";
// 		// $id = UserManagementService::create ($email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
// 		
// 		// // test FAIL
// 		// $success1 = UserManagementService::delete ( $id );
// 		// $this->assertEquals ( true, $success1, "[error] 'delete' does not work" );
// 		
// 		// $userInDB = UserManagementService::get ( $id );
// 		// $this->assertEquals ( true, $userInDB->isDeleted (), "[error] 'delete' does not work" );
// 	}
}
