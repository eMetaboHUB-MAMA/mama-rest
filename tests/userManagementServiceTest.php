<?php
require_once "bootstrap.tests.php";
require_once "../api/services/userManagementService.php";
require_once "../api/services/eventManagementService.php";
// require_once "../data-model/User.class.php";
// require_once "../vendor/autoload.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class UserManagementServiceTest extends PHPUnit_Framework_TestCase {
	
	/**
	 */
	public function testCreaded() {
		
		// create
		$email = "junit.test.create." . time () . "@inra.fr";
		$id = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
		
		// check 1
		$newUser = UserManagementService::get ( $id );
		$this->assertEquals ( $email, $newUser->getEmail (), "[error] 'create' or 'get' (by id) do not work" );
		
		// check 2
		$newUser2 = UserManagementService::getByLogin ( $email );
		$this->assertEquals ( $id, $newUser2->getId (), "[error] 'create' or 'get' (by id) do not work" );
	}
	
	/**
	 */
	public function testUpdate() {
		
		// create
		$email = "junit.test.update." . time () . "@inra.fr";
		$id = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
		
		// test FAIL
		$success1 = UserManagementService::update ( - 1, "nopTesta UPDATE", "junit UPDATE", "test UPDATE", 33, "98 76 54 32 10", "labo of unit tests UPDATE", "office truc street much UPDATE", "private", "daily_digest", true, true, true, true );
		$this->assertEquals ( false, $success1, "[error] 'create' or 'get' (by id) do not work" );
		
		// test SUCCESS
		$success2 = UserManagementService::update ( $id, null, "junit UPDATE", "test UPDATE", 33, "98 76 54 32 10", "labo of unit tests UPDATE", "office truc street much UPDATE", "private", "daily_digest", true, true, true, true );
		$this->assertEquals ( true, $success2, "[error] 'create' or 'get' (by id) do not work" );
		
		// test if update worker
		$userUpdated = UserManagementService::getByLogin ( $email );
		$this->assertEquals ( "junit UPDATE", $userUpdated->getFirstName (), "[error] 'update' does not work (firstName)" );
		$this->assertEquals ( "test UPDATE", $userUpdated->getLastName (), "[error] 'update' does not work (lastName)" );
		$this->assertEquals ( 33, $userUpdated->getPhoneGroup (), "[error] 'update' does not work (phoneGroup)" );
		$this->assertEquals ( "98 76 54 32 10", $userUpdated->getPhoneNumber (), "[error] 'update' does not work (phoneNumber)" );
		
		$this->assertEquals ( "labo of unit tests UPDATE", $userUpdated->getLaboratoryOrCompagny (), "[error] 'update' does not work (laboOrCompagny)" );
		$this->assertEquals ( "office truc street much UPDATE", $userUpdated->getWorkplaceAddress (), "[error] 'update' does not work (workplaceAddress)" );
		$this->assertEquals ( "private", $userUpdated->getLaboratoryType (), "[error] 'update' does not work (laboType)" );
		
		$this->assertEquals ( "daily_digest", $userUpdated->getEmailReception (), "[error] 'update' does not work (emailReception)" );
		$this->assertEquals ( true, $userUpdated->isEmailAlertNewUserAccount (), "[error] 'update' does not work (emailAlertNewUser)" );
		$this->assertEquals ( true, $userUpdated->isEmailAlertNewProject (), "[error] 'update' does not work (emailAlertNewProject)" );
		$this->assertEquals ( true, $userUpdated->isEmailAlertNewEventFollowedProject (), "[error] 'update' does not work (emailAlertNewEvent)" );
		$this->assertEquals ( true, $userUpdated->isEmailAlertNewMessage (), "[error] 'update' does not work (emailAlertNewMessage)" );
		
		// test update password
		$success3 = UserManagementService::update ( $id, "super coin-coin, i'm gr00t", "junit UPDATE", "test UPDATE", 33, "98 76 54 32 10", "labo of unit tests UPDATE", "office truc street much UPDATE", "private", "daily_digest", true, true, true, true );
		$this->assertEquals ( true, $success3, "[error] 'create' or 'get' (by id) do not work" );
		
		// check old password
		$this->assertEquals ( null, UserManagementService::authenticate ( $email, "nopTesta" ), "[error] 'update' do not work (password)" );
		// check new password
		$myGroot = UserManagementService::authenticate ( $email, "super coin-coin, i'm gr00t" );
		$this->assertEquals ( false, is_null ( $myGroot ), "[error] 'update' do not work (password)" );
		$this->assertEquals ( $id, $myGroot->getId (), "[error] 'update' do not work (password)" );
	}
	public function testUpdateObject() {
		
		// create
		$email = "junit.test.updateObj." . time () . "@inra.fr";
		$id = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
		
		// get & update
		$userInDB1 = UserManagementService::get ( - 1 );
		$userInDB2 = UserManagementService::get ( $id );
		
		$userInDB2->setFirstName ( "junit UPDATE" );
		$userInDB2->setLastName ( "test UPDATE" );
		$userInDB2->setPhoneGroup ( 33 );
		$userInDB2->setPhoneNumber ( "98 76 54 32 10" );
		
		$userInDB2->setLaboratoryOrCompagny ( "labo of unit tests UPDATE" );
		$userInDB2->setWorkplaceAddress ( "office truc street much UPDATE" );
		$userInDB2->setLaboratoryType ( "private" );
		
		$userInDB2->setEmailReception ( "daily_digest" );
		$userInDB2->setEmailAlert ( true, true, true, true );
		
		// $userInDB2->setStatus ( "active" );
		// $userInDB2->setRight ( "admin" );
		
		// test FAIL
		$success1 = UserManagementService::updateObject ( $userInDB1, true );
		$this->assertEquals ( false, $success1, "[error] 'create' or 'get' (by id) do not work" );
		
		// test SUCCESS
		$success2 = UserManagementService::updateObject ( $userInDB2, false );
		$this->assertEquals ( true, $success2, "[error] 'create' or 'get' (by id) do not work" );
		
		// test if update worked
		$userUpdated = UserManagementService::getByLogin ( $email );
		$this->assertEquals ( "junit UPDATE", $userUpdated->getFirstName (), "[error] 'updateObject' does not work (firstName)" );
		$this->assertEquals ( "test UPDATE", $userUpdated->getLastName (), "[error] 'updateObject' does not work (lastName)" );
		$this->assertEquals ( 33, $userUpdated->getPhoneGroup (), "[error] 'updateObject' does not work (phoneGroup)" );
		$this->assertEquals ( "98 76 54 32 10", $userUpdated->getPhoneNumber (), "[error] 'updateObject' does not work (phoneNumber)" );
		
		$this->assertEquals ( "labo of unit tests UPDATE", $userUpdated->getLaboratoryOrCompagny (), "[error] 'updateObject' does not work (laboOrCompagny)" );
		$this->assertEquals ( "office truc street much UPDATE", $userUpdated->getWorkplaceAddress (), "[error] 'updateObject' does not work (workplaceAddress)" );
		$this->assertEquals ( "private", $userUpdated->getLaboratoryType (), "[error] 'updateObject' does not work (laboType)" );
		
		$this->assertEquals ( "daily_digest", $userUpdated->getEmailReception (), "[error] 'updateObject' does not work (emailReception)" );
		$this->assertEquals ( true, $userUpdated->isEmailAlertNewUserAccount (), "[error] 'updateObject' does not work (emailAlertNewUser)" );
		$this->assertEquals ( true, $userUpdated->isEmailAlertNewProject (), "[error] 'updateObject' does not work (emailAlertNewProject)" );
		$this->assertEquals ( true, $userUpdated->isEmailAlertNewEventFollowedProject (), "[error] 'updateObject' does not work (emailAlertNewEvent)" );
		$this->assertEquals ( true, $userUpdated->isEmailAlertNewMessage (), "[error] 'updateObject' does not work (emailAlertNewMessage)" );
		
		$this->assertEquals ( false, $userUpdated->isActive (), "[error] 'updateObject' does not work (status)" );
		$this->assertEquals ( false, $userUpdated->isAdmin (), "[error] 'updateObject' does not work (right)" );
		
		// set admin
		$userUpdated2 = UserManagementService::getByLogin ( $email );
		$userUpdated2->setStatus ( "active" );
		$userUpdated2->setRight ( "admin" );
		
		$success3 = UserManagementService::updateObject ( $userUpdated2, true );
		$this->assertEquals ( true, $success3, "[error] 'create' or 'get' (by id) do not work" );
		
		// test admin mode
		$this->assertEquals ( true, $userUpdated2->isActive (), "[error] 'updateObject' does not work (status)" );
		$this->assertEquals ( true, $userUpdated2->isAdmin (), "[error] 'updateObject' does not work (right)" );
	}
	
	/**
	 */
	public function testDelete() {
		// create
		$email = "junit.test.delete." . time () . "@inra.fr";
		$id = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
		
		// test FAIL
		$success1 = UserManagementService::delete ( $id );
		$this->assertEquals ( true, $success1, "[error] 'delete' does not work" );
		
		$userInDB = UserManagementService::get ( $id );
		$this->assertEquals ( true, $userInDB->isDeleted (), "[error] 'delete' does not work" );
	}
	
	/**
	 */
	public function testFind() {
		
		// create
		$email = "junit.test.find." . time () . "@inra.fr";
		$id = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
		
		// by id
		$newUser = UserManagementService::get ( $id );
		$this->assertEquals ( $email, $newUser->getEmail (), "[error] 'create' or 'get' (by id) do not work" );
		
		// by email
		$newUser2 = UserManagementService::getByLogin ( $email );
		$this->assertEquals ( $id, $newUser2->getId (), "[error] 'create' or 'get' (by id) do not work" );
	}
	
	/**
	 */
	public function testFindAll() {
		
		// create 3 users and deleted 2
		$email1 = "junit.test.find1." . time () . "@inra.fr";
		$id1 = UserManagementService::create ( $email1, $email1, "", null, null, null, null, null, null, null );
		$email2 = "junit.test.find2." . time () . "@inra.fr";
		$id2 = UserManagementService::create ( $email2, $email2, "", null, null, null, null, null, null, null );
		$email3 = "junit.test.find3" . time () . "@inra.fr";
		$id3 = UserManagementService::create ( $email3, $email3, "", null, null, null, null, null, null, null );
		
		UserManagementService::delete ( $id1 );
		UserManagementService::delete ( $id2 );
		
		// without filters
		$usersAll = UserManagementService::getUsers ();
		$nbUser = count ( $usersAll );
		$this->assertEquals ( true, $nbUser > 0, "[error] gets() return empty list (or no users in test database)" );
		
		// with filters
		$_GET ["start"] = intval ( ($nbUser / 2) );
		$_GET ["limit"] = 30;
		$usersFilter = UserManagementService::getUsers ();
		$this->assertEquals ( true, ($usersFilter [0]->getId () != $usersAll [0]->getId ()), "[error] filter 'start' does not work" );
		$this->assertEquals ( true, (count ( $usersFilter ) < count ( $usersAll )), "[error] filters 'start' and 'limit' do not work" );
		
		// with filter deleted
		unset ( $_GET ["start"] );
		unset ( $_GET ["limit"] );
		$_GET ["deleted"] = true;
		$usersFilterDeleted = UserManagementService::getUsers ();
		
		$find1 = false;
		$find2 = false;
		$find3 = false;
		foreach ( $usersFilterDeleted as $key => $val ) {
			$this->assertEquals ( true, $val->isDeleted (), "[error] filters 'deleted' do not work" );
			if ($val->getId () == $id1)
				$find1 = true;
			if ($val->getId () == $id2)
				$find2 = true;
			if ($val->getId () == $id3)
				$find3 = true;
		}
		$this->assertEquals ( true, $find1, "[error] filters 'deleted' do not work" );
		$this->assertEquals ( true, $find2, "[error] filters 'deleted' do not work" );
		$this->assertEquals ( false, $find3, "[error] filters 'deleted' do not work" );
	}
}