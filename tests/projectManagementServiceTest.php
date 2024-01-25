<?php
require_once "bootstrap.tests.php";
require_once "../api/services/userManagementService.php";
require_once "../api/services/projectManagementService.php";
require_once "../api/services/eventManagementService.php";
// require_once "../data-model/User.class.php";
// require_once "../vendor/autoload.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class ProjectManagementServiceTest extends PHPUnit_Framework_TestCase {
	
	/**
	 */
	public function testCreated() {
		
		// create user
		$email = "junit.test.create." . time () . "@inra.fr";
		$idUser = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
		$user = UserManagementService::get ( $idUser );
		
		// create
		// What would you like to do with MTH?
		$mthStuff = "create MAMA REST API UNIT TEST, just because love Chuck Testa";
		// type of demand
		$demand_type_eq = false;
		$demand_type_labRout = true;
		$demand_type_feasibility = false;
		$demand_type_formation = false;
		$demand_type_data_processing = false;
		$demand_type_other = true;
		
		// number of sample
		$demand_sample_nb = "50 or fewer";
		// thematic cloud word
		$cloudWords = null;
		// targeted
		$targeted = false;
		// MTH plateform(s)
		$mthPF = null;
		// Can the analysis request be forwarded to copartner
		$forwardAR2copartner = false;
		// scientific context
		$scientificContext = "pancakes, i need pancakes!!!";
		// scientific contextFile
		$scientificContextFile = null;
		// Financial context
		$financialContextIsProjectFinanced = false;
		$financialContextIsProjectInProvisioning = false;
		$financialContextIsProjectOnOwnSupply = false;
		$financialContextIsProjectNotFinanced = true;
		// Financial context (bis)
		$financialContextIsProjectEU = false;
		$financialContextIsProjectANR = true;
		$financialContextIsProjectNational = false;
		$financialContextIsProjectRegional = false;
		$financialContextIsProjectCompagnyTutorship = false;
		$financialContextIsProjectOther = true;
		// Other Financial
		$financialContextIsProjectOtherValue = "rainbows and unicorns";
		
		// create
		$title = "junit.test.create." . time () . " TITLE";
		$idProject = ProjectManagementService::create ( $title, $user, $mthStuff, $demand_type_eq, $demand_type_labRout, $demand_type_feasibility, $demand_type_formation, $demand_type_data_processing, $demand_type_other, $demand_sample_nb, $cloudWords, $targeted, $mthPF, $forwardAR2copartner, $scientificContext, $scientificContextFile, $financialContextIsProjectFinanced, $financialContextIsProjectInProvisioning, $financialContextIsProjectOnOwnSupply, $financialContextIsProjectNotFinanced, $financialContextIsProjectEU, $financialContextIsProjectANR, $financialContextIsProjectNational, $financialContextIsProjectRegional, $financialContextIsProjectCompagnyTutorship, $financialContextIsProjectOther, $financialContextIsProjectOtherValue );
		
		// check 1
		$newProject = ProjectManagementService::get ( $idProject );
		$this->assertEquals ( $title, $newProject->getTitle (), "[error] 'create' or 'get' (by id) does not work" );
		
		// check 2
		$newProject2 = ProjectManagementService::search ( $title );
		$project = $newProject2 [0];
		$this->assertEquals ( $idProject, $project->getId (), "[error] 'create' or 'get' (by id) does not work" );
	}
	
	/**
	 */
	public function testUpdate() {
		
		// create user
		$email = "junit.test.create2." . time () . "@inra.fr";
		$idUser = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
		$user = UserManagementService::get ( $idUser );
		
		// create
		// What would you like to do with MTH?
		$mthStuff = "create MAMA REST API UNIT TEST, just because love Chuck Testa";
		// type of demand
		$demand_type_eq = false;
		$demand_type_labRout = true;
		$demand_type_feasibility = false;
		$demand_type_formation = false;
		$demand_type_data_processing = false;
		$demand_type_other = true;
		// number of sample
		$demand_sample_nb = "50 or fewer";
		// thematic cloud word
		$cloudWords = null;
		// targeted
		$targeted = false;
		// MTH plateform(s)
		$mthPF = null;
		// Can the analysis request be forwarded to copartner
		$forwardAR2copartner = false;
		// scientific context
		$scientificContext = "pancakes, i need pancakes!!!";
		// scientific contextFile
		$scientificContextFile = null;
		// Financial context
		$financialContextIsProjectFinanced = false;
		$financialContextIsProjectInProvisioning = false;
		$financialContextIsProjectOnOwnSupply = false;
		$financialContextIsProjectNotFinanced = true;
		// Financial context (bis)
		$financialContextIsProjectEU = false;
		$financialContextIsProjectANR = true;
		$financialContextIsProjectNational = false;
		$financialContextIsProjectRegional = false;
		$financialContextIsProjectCompagnyTutorship = false;
		$financialContextIsProjectInternationalOutsideEU = false;
		$financialContextIsProjectOwnResourcesLaboratory = false;
		$financialContextIsProjectOther = true;
		// Other Financial
		$financialContextIsProjectOtherValue = "rainbows and unicorns";
		// create
		$title = "junit.test.create." . time () . " TITLE";
		$idProject = ProjectManagementService::create ( $title, $user, $mthStuff, $demand_type_eq, $demand_type_labRout, $demand_type_feasibility, $demand_type_formation, $demand_type_data_processing, $demand_type_other, $demand_sample_nb, $cloudWords, $targeted, $mthPF, $forwardAR2copartner, $scientificContext, $scientificContextFile, $financialContextIsProjectFinanced, $financialContextIsProjectInProvisioning, $financialContextIsProjectOnOwnSupply, $financialContextIsProjectNotFinanced, $financialContextIsProjectEU, $financialContextIsProjectANR, $financialContextIsProjectNational, $financialContextIsProjectRegional, $financialContextIsProjectCompagnyTutorship, $financialContextIsProjectnternationalOutsideEU, $financialContextIsProjectOwnResourcesLaboratory, $financialContextIsProjectOther, $financialContextIsProjectOtherValue );
		
		// test FAIL
		$success1 = ProjectManagementService::update ( - 1, "bob-marcel dans la place", "free vacation for bob-marcel", true, false, true, true, null, null, true, null, false, "ADC power", "nope", false, false, false, true, false, false, true, false, false, false, false, true, "abonnement VIC" );
		$this->assertEquals ( false, $success1, "[error] 'create' or 'get' (by id) does not work" );
		
		// test SUCCESS
		$success2 = ProjectManagementService::update ( $idProject, "bob-marcel à la plage", "free vacation for bob-marcel", true, false, true, true, null, null, true, null, false, "ADC power", "nope", false, false, false, true, false, false, true, false, false, false, false, true, "abonnement VIC" );
		$this->assertEquals ( true, $success2, "[error] 'create' or 'get' (by id) does not work" );
		
		// test if update worker
		$projectUpdated = ProjectManagementService::get ( $idProject );
		$this->assertEquals ( "bob-marcel à la plage", $projectUpdated->getTitle (), "[error] 'update' does not work (title)" );
		$this->assertEquals ( "free vacation for bob-marcel", $projectUpdated->getInterestInMthCollaboration (), "[error] 'update' does not work (inMthCollaboration)" );
		$this->assertEquals ( true, $projectUpdated->getDemandTypeEqProvisioning (), "[error] 'update' does not work (getDemandTypeEqProvisioning)" );
		$this->assertEquals ( false, $projectUpdated->getDemandTypeCatalogAllowance (), "[error] 'update' does not work (getDemandTypeCatalogAllowance)" );
		$this->assertEquals ( true, $projectUpdated->getDemandTypeFeasibilityStudy (), "[error] 'update' does not work (getDemandTypeFeasibilityStudy)" );
		$this->assertEquals ( true, $projectUpdated->getDemandTypeTraining (), "[error] 'update' does not work (getDemandTypeTraining)" );
		$this->assertEquals ( null, $projectUpdated->getSamplesNumber (), "[error] 'update' does not work (getSamplesNumber)" );
		// $this->assertEquals ( true, is_object ( $projectUpdated->getThematicWords () ), "[error] 'update' does not work (getThematicWords)" );
		$this->assertEquals ( 0, sizeof ( $projectUpdated->getThematicWords () ), "[error] 'update' does not work (getThematicWords)" );
		$this->assertEquals ( true, $projectUpdated->getTargeted (), "[error] 'update' does not work (getTargeted)" );
		// $this->assertEquals ( true, is_object ( $projectUpdated->getMthPlatforms () ), "[error] 'update' does not work (getMthPlatforms)" );
		$this->assertEquals ( 0, sizeof ( $projectUpdated->getMthPlatforms () ), "[error] 'update' does not work (getMthPlatforms)" );
		$this->assertEquals ( false, $projectUpdated->getCanBeForwardedToCoPartner (), "[error] 'update' does not work (getCanBeForwardedToCoPartner)" );
		$this->assertEquals ( "ADC power", $projectUpdated->getScientificContext (), "[error] 'update' does not work (getScientificContext)" );
		$this->assertEquals ( "nope", $projectUpdated->getScientificContextFile (), "[error] 'update' does not work (getScientificContextFile)" );
		$this->assertEquals ( false, $projectUpdated->getFinancialContextIsProjectFinanced (), "[error] 'update' does not work (getFinancialContextIsProjectFinanced)" );
		$this->assertEquals ( false, $projectUpdated->getFinancialContextIsProjectInProvisioning (), "[error] 'update' does not work (getFinancialContextIsProjectInProvisioning)" );
		$this->assertEquals ( false, $projectUpdated->getFinancialContextIsProjectOnOwnSupply (), "[error] 'update' does not work (getFinancialContextIsProjectOnOwnSupply)" );
		$this->assertEquals ( true, $projectUpdated->getFinancialContextIsProjectNotFinanced (), "[error] 'update' does not work (getFinancialContextIsProjectNotFinanced)" );
		$this->assertEquals ( false, $projectUpdated->getFinancialContextIsProjectEU (), "[error] 'update' does not work (getFinancialContextIsProjectEU)" );
		$this->assertEquals ( false, $projectUpdated->getFinancialContextIsProjectANR (), "[error] 'update' does not work (getFinancialContextIsProjectANR)" );
		$this->assertEquals ( true, $projectUpdated->getFinancialContextIsProjectNational (), "[error] 'update' does not work (getFinancialContextIsProjectNational)" );
		$this->assertEquals ( false, $projectUpdated->getFinancialContextIsProjectRegional (), "[error] 'update' does not work (getFinancialContextIsProjectRegional)" );
		$this->assertEquals ( false, $projectUpdated->getFinancialContextIsProjectInternationalOutsideEU (), "[error] 'update' does not work (getFinancialContextIsProjectInternationalOutsideEU)" );
		$this->assertEquals ( false, $projectUpdated->getFinancialContextIsProjectOwnResourcesLaboratory (), "[error] 'update' does not work (getFinancialContextIsProjectOwnResourcesLaboratory)" );
		$this->assertEquals ( true, $projectUpdated->getFinancialContextIsProjectOther (), "[error] 'update' does not work (getFinancialContextIsProjectOther)" );
		$this->assertEquals ( "abonnement VIC", $projectUpdated->getFinancialContextIsProjectOtherValue (), "[error] 'update' does not work (getFinancialContextIsProjectOtherValue)" );
	}
	// public function testUpdateObject() {
	
	// // create
	// $email = "junit.test.updateObj." . time () . "@inra.fr";
	// $id = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
	
	// // get & update
	// $userInDB1 = UserManagementService::get ( - 1 );
	// $userInDB2 = UserManagementService::get ( $id );
	
	// $userInDB2->setFirstName ( "junit UPDATE" );
	// $userInDB2->setLastName ( "test UPDATE" );
	// $userInDB2->setPhoneGroup ( 33 );
	// $userInDB2->setPhoneNumber ( "98 76 54 32 10" );
	
	// $userInDB2->setLaboratoryOrCompagny ( "labo of unit tests UPDATE" );
	// $userInDB2->setWorkplaceAddress ( "office truc street much UPDATE" );
	// $userInDB2->setLaboratoryType ( "private" );
	
	// $userInDB2->setEmailReception ( "daily_digest" );
	// $userInDB2->setEmailAlert ( true, true, true, true );
	
	// // $userInDB2->setStatus ( "active" );
	// // $userInDB2->setRight ( "admin" );
	
	// // test FAIL
	// $success1 = UserManagementService::updateObject ( $userInDB1, true );
	// $this->assertEquals ( false, $success1, "[error] 'create' or 'get' (by id) does not work" );
	
	// // test SUCCESS
	// $success2 = UserManagementService::updateObject ( $userInDB2, false );
	// $this->assertEquals ( true, $success2, "[error] 'create' or 'get' (by id) does not work" );
	
	// // test if update worked
	// $userUpdated = UserManagementService::getByLogin ( $email );
	// $this->assertEquals ( "junit UPDATE", $userUpdated->getFirstName (), "[error] 'updateObject' does not work (firstName)" );
	// $this->assertEquals ( "test UPDATE", $userUpdated->getLastName (), "[error] 'updateObject' does not work (lastName)" );
	// $this->assertEquals ( 33, $userUpdated->getPhoneGroup (), "[error] 'updateObject' does not work (phoneGroup)" );
	// $this->assertEquals ( "98 76 54 32 10", $userUpdated->getPhoneNumber (), "[error] 'updateObject' does not work (phoneNumber)" );
	
	// $this->assertEquals ( "labo of unit tests UPDATE", $userUpdated->getLaboratoryOrCompagny (), "[error] 'updateObject' does not work (laboOrCompagny)" );
	// $this->assertEquals ( "office truc street much UPDATE", $userUpdated->getWorkplaceAddress (), "[error] 'updateObject' does not work (workplaceAddress)" );
	// $this->assertEquals ( "private", $userUpdated->getLaboratoryType (), "[error] 'updateObject' does not work (laboType)" );
	
	// $this->assertEquals ( "daily_digest", $userUpdated->getEmailReception (), "[error] 'updateObject' does not work (emailReception)" );
	// $this->assertEquals ( true, $userUpdated->isEmailAlertNewUserAccount (), "[error] 'updateObject' does not work (emailAlertNewUser)" );
	// $this->assertEquals ( true, $userUpdated->isEmailAlertNewProject (), "[error] 'updateObject' does not work (emailAlertNewProject)" );
	// $this->assertEquals ( true, $userUpdated->isEmailAlertNewEventFollowedProject (), "[error] 'updateObject' does not work (emailAlertNewEvent)" );
	// $this->assertEquals ( true, $userUpdated->isEmailAlertNewMessage (), "[error] 'updateObject' does not work (emailAlertNewMessage)" );
	
	// $this->assertEquals ( false, $userUpdated->isActive (), "[error] 'updateObject' does not work (status)" );
	// $this->assertEquals ( false, $userUpdated->isAdmin (), "[error] 'updateObject' does not work (right)" );
	
	// // set admin
	// $userUpdated2 = UserManagementService::getByLogin ( $email );
	// $userUpdated2->setStatus ( "active" );
	// $userUpdated2->setRight ( "admin" );
	
	// $success3 = UserManagementService::updateObject ( $userUpdated2, true );
	// $this->assertEquals ( true, $success3, "[error] 'create' or 'get' (by id) does not work" );
	
	// // test admin mode
	// $this->assertEquals ( true, $userUpdated2->isActive (), "[error] 'updateObject' does not work (status)" );
	// $this->assertEquals ( true, $userUpdated2->isAdmin (), "[error] 'updateObject' does not work (right)" );
	// }
	
	// /**
	// */
	// public function testDelete() {
	// // create
	// $email = "junit.test.delete." . time () . "@inra.fr";
	// $id = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
	
	// // test FAIL
	// $success1 = UserManagementService::delete ( $id );
	// $this->assertEquals ( true, $success1, "[error] 'delete' does not work" );
	
	// $userInDB = UserManagementService::get ( $id );
	// $this->assertEquals ( true, $userInDB->isDeleted (), "[error] 'delete' does not work" );
	// }
	
	// /**
	// */
	// public function testFind() {
	
	// // create
	// $email = "junit.test.find." . time () . "@inra.fr";
	// $id = UserManagementService::create ( $email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public" );
	
	// // by id
	// $newUser = UserManagementService::get ( $id );
	// $this->assertEquals ( $email, $newUser->getEmail (), "[error] 'create' or 'get' (by id) does not work" );
	
	// // by email
	// $newUser2 = UserManagementService::getByLogin ( $email );
	// $this->assertEquals ( $id, $newUser2->getId (), "[error] 'create' or 'get' (by id) does not work" );
	// }
	
	// /**
	// */
	// public function testFindAll() {
	
	// // create 3 users and deleted 2
	// $email1 = "junit.test.find1." . time () . "@inra.fr";
	// $id1 = UserManagementService::create ( $email1, $email1, "", null, null, null, null, null, null, null );
	// $email2 = "junit.test.find2." . time () . "@inra.fr";
	// $id2 = UserManagementService::create ( $email2, $email2, "", null, null, null, null, null, null, null );
	// $email3 = "junit.test.find3" . time () . "@inra.fr";
	// $id3 = UserManagementService::create ( $email3, $email3, "", null, null, null, null, null, null, null );
	
	// UserManagementService::delete ( $id1 );
	// UserManagementService::delete ( $id2 );
	
	// // without filters
	// $usersAll = UserManagementService::getUsers ();
	// $nbUser = count ( $usersAll );
	// $this->assertEquals ( true, $nbUser > 0, "[error] gets() return empty list (or no users in test database)" );
	
	// // with filters
	// $_GET ["start"] = intval ( ($nbUser / 2) );
	// $_GET ["limit"] = 30;
	// $usersFilter = UserManagementService::getUsers ();
	// $this->assertEquals ( true, ($usersFilter [0]->getId () != $usersAll [0]->getId ()), "[error] filter 'start' does not work" );
	// $this->assertEquals ( true, (count ( $usersFilter ) < count ( $usersAll )), "[error] filters 'start' and 'limit' does not work" );
	
	// // with filter deleted
	// unset ( $_GET ["start"] );
	// unset ( $_GET ["limit"] );
	// $_GET ["deleted"] = true;
	// $usersFilterDeleted = UserManagementService::getUsers ();
	
	// $find1 = false;
	// $find2 = false;
	// $find3 = false;
	// foreach ( $usersFilterDeleted as $key => $val ) {
	// $this->assertEquals ( true, $val->isDeleted (), "[error] filters 'deleted' does not work" );
	// if ($val->getId () == $id1)
	// $find1 = true;
	// if ($val->getId () == $id2)
	// $find2 = true;
	// if ($val->getId () == $id3)
	// $find3 = true;
	// }
	// $this->assertEquals ( true, $find1, "[error] filters 'deleted' does not work" );
	// $this->assertEquals ( true, $find2, "[error] filters 'deleted' does not work" );
	// $this->assertEquals ( false, $find3, "[error] filters 'deleted' does not work" );
	// }
}