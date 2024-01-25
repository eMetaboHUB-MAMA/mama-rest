<?php
require_once "bootstrap.tests.php";
require_once "../api/services/emailManagementService.php";
// require_once "../data-model/User.class.php";
// require_once "../vendor/autoload.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class EmailManagementServiceTest extends PHPUnit_Framework_TestCase {
	
	/**
	 */
	public function testSend() {
		$success1 = EmailManagementService::sendEmailAccountCreation ( "nils.paulhe.FAIL@clermont.inra.fr", "Nils Paulhe",  "en" );
		$success2 = EmailManagementService::sendEmailAccountCreation ( "nils.paulhe@clermont.inra.fr", "Nils Paulhe", "en" );
		
		// check if old is deleted
		$this->assertEquals ( false, $success1, "[error] could send an email to an INVALIDE address" );
		$this->assertEquals ( true, $success2, "[error] could not send an email to a VALIDE address" );
		// $this->assertEquals ( false, TokenManagementService::isValide ( $token->getValue () ), "[error] 'create' or 'getForUser' (by id) do not work" );
		// $this->assertEquals ( true, TokenManagementService::isValide ( $tokenNew->getValue () ), "[error] 'create' or 'getForUser' (by id) do not work" );
	}
}