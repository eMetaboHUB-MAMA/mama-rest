<?php
require_once ('../data-model/Project.class.php');
require_once ('../data-model/ProjectExtraData.class.php');

require_once ('../api/utils/format.php');

/**
 * Static test suite.
 */
class ProjectExtraDataClassTest extends PHPUnit\Framework\TestCase
{

    public function testConstructor()
    {
        $t = new Project("junit-test", null);
        $obj = new ProjectExtraData($t);
        $this->assertNotNull($obj);
    }

    public function testGetterSetters()
    {
        $t = new Project("junit-test", null);
        $obj = new ProjectExtraData($t);
        // getters 1
        $this->assertEquals(null, $obj->getAdministrativeContext());
        $this->assertEquals(null, $obj->getBlockedReason());
        $this->assertEquals(null, $obj->getBudgetConstraint());
        $this->assertEquals(null, $obj->getDeadline());
        $this->assertEquals(null, $obj->getDialogBoxTxt());
        $this->assertEquals("", $obj->getDialogBoxVal());
        $this->assertEquals(null, $obj->getGeographicContext());
        $this->assertEquals(null, $obj->getKnowMTHviaCoworkerOrFriend());
        $this->assertEquals(null, $obj->getKnowMTHviaPublication());
        $this->assertEquals(null, $obj->getKnowMTHviaSearchEngine());
        $this->assertEquals(null, $obj->getKnowMTHviaWebsite());
        $this->assertEquals("", $obj->getLaboType());
        $this->assertEquals(null, $obj->getProjectMaturity());
        $this->assertEquals(null, $obj->getRejectedReason());
        $this->assertEquals(null, $obj->getStoppedReason());
        $this->assertEquals(null, $obj->getSyntheticUserNeeds());
        // setters
        $obj->setAdministrativeContext("setAdministrativeContext");
        $obj->setBlockedReason("setBlockedReason");
        $obj->setBudgetConstraint("setBudgetConstraint");
        $obj->setDeadline("setDeadline");
        $obj->setDialogBoxTxt("setDialogBoxTxt");
        $obj->setDialogBoxVal("setDialogBoxVal");
        $obj->setGeographicContext("setGeographicContext");
        $obj->setKnowMTHviaCoworkerOrFriend(true);
        $obj->setKnowMTHviaPublication(false);
        $obj->setKnowMTHviaSearchEngine(true);
        $obj->setKnowMTHviaWebsite(false);
        $obj->setLaboType("setLaboType");
        $obj->setProjectMaturity("setProjectMaturity");
        $obj->setRejectedReason("setRejectedReason");
        $obj->setStoppedReason("setStoppedReason");
        $obj->setSyntheticUserNeeds("setSyntheticUserNeeds");
        // getters 2
        $this->assertEquals("setAdministrativeContext", $obj->getAdministrativeContext());
        $this->assertEquals("", $obj->getBlockedReason());
        $this->assertEquals("setBudgetConstraint", $obj->getBudgetConstraint());
        $this->assertEquals("setDeadline", $obj->getDeadline());
        $this->assertEquals("setDialogBoxTxt", $obj->getDialogBoxTxt());
        $this->assertEquals("", $obj->getDialogBoxVal());
        $this->assertEquals("setGeographicContext", $obj->getGeographicContext());
        $this->assertEquals(true, $obj->getKnowMTHviaCoworkerOrFriend());
        $this->assertEquals(false, $obj->getKnowMTHviaPublication());
        $this->assertEquals(true, $obj->getKnowMTHviaSearchEngine());
        $this->assertEquals(false, $obj->getKnowMTHviaWebsite());
        $this->assertEquals("", $obj->getLaboType());
        $this->assertEquals("setProjectMaturity", $obj->getProjectMaturity());
        $this->assertEquals("", $obj->getRejectedReason());
        $this->assertEquals("setStoppedReason", $obj->getStoppedReason());
        $this->assertEquals("setSyntheticUserNeeds", $obj->getSyntheticUserNeeds());
        //
        ob_start();
        var_dump($obj->getArrayData());
        $getArrayData = ob_get_clean();
        $getArrayData = preg_replace("/DateTime#\d+|ClassTest.php:\d+/", "x", $getArrayData);
        $this->assertStringStartsWith(preg_replace("/DateTime#\d+|ClassTest.php:\d+/", "x", '/var/www/html/tests/ProjectExtraDataClassTest.php:77:
array(19) {
  \'laboType\' =>
  string(0) ""
  \'administrativeContext\' =>
  string(24) "setAdministrativeContext"
  \'geographicContext\' =>
  string(20) "setGeographicContext"
  \'knowMTHviaCoworkerOrFriend\' =>
  bool(true)
  \'knowMTHviaPublication\' =>
  bool(false)
  \'knowMTHviaWebsite\' =>
  bool(false)
  \'knowMTHviaSearchEngine\' =>
  bool(true)
  \'syntheticUserNeeds\' =>
  string(21) "setSyntheticUserNeeds"
  \'projectMaturity\' =>
  string(18) "setProjectMaturity"
  \'deadline\' =>
  string(11) "setDeadline"
  \'budgetConstraint\' =>
  string(19) "setBudgetConstraint"
  \'blockedReason\' =>
  string(0) ""
  \'rejectedReason\' =>
  string(0) ""
  \'stoppedReason\' =>
  string(16) "setStoppedReason"
  \'dialogBoxVal\' =>
  string(0) ""
  \'dialogBoxTxt\' =>
  string(15) "setDialogBoxTxt"
  \'id\' =>
  int(0)
  \'created\' =>
'), $getArrayData);
        
        $this->assertStringEndsWith(preg_replace("/DateTime#\d+|ClassTest.php:\d+/", "x", '
  }
  \'updated\' =>
  NULL
}
'), $getArrayData);

        ob_start();
        var_dump($obj->getJsonData());
        $getJsonData = ob_get_clean();
        $getJsonData = preg_replace("/DateTime#\d+|ClassTest.php:\d+/", "x", $getJsonData);
        $this->assertEquals(preg_replace("/DateTime#\d+|ClassTest.php:\d+/", "x", '/var/www/html/tests/ProjectExtraDataClassTest.php:124:
array(19) {
  \'laboType\' =>
  string(6) "public"
  \'administrativeContext\' =>
  string(24) "setAdministrativeContext"
  \'geographicContext\' =>
  string(20) "setGeographicContext"
  \'knowMTHviaCoworkerOrFriend\' =>
  bool(true)
  \'knowMTHviaPublication\' =>
  bool(false)
  \'knowMTHviaWebsite\' =>
  bool(false)
  \'knowMTHviaSearchEngine\' =>
  bool(true)
  \'syntheticUserNeeds\' =>
  string(21) "setSyntheticUserNeeds"
  \'projectMaturity\' =>
  string(18) "setProjectMaturity"
  \'deadline\' =>
  string(11) "setDeadline"
  \'budgetConstraint\' =>
  string(19) "setBudgetConstraint"
  \'blockedReason\' =>
  string(0) ""
  \'rejectedReason\' =>
  string(0) ""
  \'stoppedReason\' =>
  string(16) "setStoppedReason"
  \'dialogBoxVal\' =>
  string(0) ""
  \'dialogBoxTxt\' =>
  string(15) "setDialogBoxTxt"
  \'id\' =>
  int(0)
  \'created\' =>
  class DateTime#1072 (3) {
    public $date =>
    string(26) "' . $obj->getCreated()
            ->format('Y-m-d H:i:s.u') . '"
    public $timezone_type =>
    int(3)
    public $timezone =>
    string(3) "UTC"
  }
  \'updated\' =>
  NULL
}
'), $getJsonData);
    }

    public function testGetSetLaboType()
    {
        $t = new Project("junit-test", null);
        $obj = new ProjectExtraData($t);
        // init
        $this->assertEquals("", $obj->getLaboType());

        // getters / setters 1
        $obj->setLaboType(ProjectExtraData::$LABO_TYPE_PRIVATE);
        $this->assertEquals("private", $obj->getLaboType());
        $obj->setLaboType(ProjectExtraData::$LABO_TYPE_PUBLIC);
        $this->assertEquals("public", $obj->getLaboType());
        $obj->setLaboType(ProjectExtraData::$LABO_TYPE_PRIVATE_PUBLIC);
        $this->assertEquals("private/public", $obj->getLaboType());

        // reset 1
        $obj->setLaboType(- 1);
        $this->assertEquals("", $obj->getLaboType());

        // getters / setters 2
        $obj->setLaboType("PrIvate");
        $this->assertEquals("private", $obj->getLaboType());
        $obj->setLaboType("PUBLIC");
        $this->assertEquals("public", $obj->getLaboType());
        $obj->setLaboType("private/PUBLIC");
        $this->assertEquals("private/public", $obj->getLaboType());

        // reset 2
        $obj->setLaboType("toto");
        $this->assertEquals("", $obj->getLaboType());
    }

    public function testGetSetBlockedReason()
    {
        $t = new Project("junit-test", null);
        $obj = new ProjectExtraData($t);
        // init
        $this->assertEquals("", $obj->getBlockedReason());

        // getters / setters 1
        $obj->setBlockedReason(ProjectExtraData::$PAUSED_REASON__WAITING_FOR_SAMPLES);
        $this->assertEquals("waiting_for_samples", $obj->getBlockedReason());
        $obj->setBlockedReason(ProjectExtraData::$PAUSED_REASON__WAITING_FOR_SERVICE_USER_ANSWER);
        $this->assertEquals("waiting_for_service_user_answer", $obj->getBlockedReason());
        $obj->setBlockedReason(ProjectExtraData::$PAUSED_REASON__WAITING_FOR_PROVISIONING);
        $this->assertEquals("waiting_for_provisioning", $obj->getBlockedReason());

        // reset 1
        $obj->setBlockedReason(- 1);
        $this->assertEquals("", $obj->getBlockedReason());

        // getters / setters 2
        $obj->setBlockedReason("_waiting_FOR_samples");
        $this->assertEquals("waiting_for_samples", $obj->getBlockedReason());
        $obj->setBlockedReason("_waiting_for_service_user_answer");
        $this->assertEquals("waiting_for_service_user_answer", $obj->getBlockedReason());
        $obj->setBlockedReason("_waiting_for_provisioning");
        $this->assertEquals("waiting_for_provisioning", $obj->getBlockedReason());

        // reset 2
        $obj->setBlockedReason("toto");
        $this->assertEquals("", $obj->getBlockedReason());
    }

    public function testGetSetRejectedReason()
    {
        $t = new Project("junit-test", null);
        $obj = new ProjectExtraData($t);
        // init
        $this->assertEquals("", $obj->getRejectedReason());

        // getters / setters 1
        $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__TOO_EXPENSIVE);
        $this->assertEquals("too_expensive", $obj->getRejectedReason());
        $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__TOO_LONG_DELAYS);
        $this->assertEquals("too_long_delays", $obj->getRejectedReason());
        // $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__NOT_ENOUGH_TIME);
        // $this->assertEquals("not_enough_time", $obj->getRejectedReason());
        $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__OUTSIDE_OUR_SKILL_SPHERE);
        $this->assertEquals("outside_our_skill_sphere", $obj->getRejectedReason());
        $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__NON_PRIORITARY_RAD);
        $this->assertEquals("non_prioritary_rad", $obj->getRejectedReason());
        $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__INCOMPATIBLE_DEADLINE);
        $this->assertEquals("incompatible_deadline", $obj->getRejectedReason());
        $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__TOO_MANY_SAMPLES);
        $this->assertEquals("too_many_samples", $obj->getRejectedReason());
        $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__TRANSFERED_TO_PRIVILEGIED_MTH_PARTNER);
        $this->assertEquals("transfered_to_privilegied_mth_partner", $obj->getRejectedReason());
        $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__NOT_FUNDED);
        $this->assertEquals("not_funded", $obj->getRejectedReason());
        $obj->setRejectedReason(ProjectExtraData::$REJECTED_REASON__SAVED_TWICE);
        $this->assertEquals("saved_twice", $obj->getRejectedReason());

        // reset 1
        $obj->setRejectedReason(- 1);
        $this->assertEquals("", $obj->getRejectedReason());

        // getters / setters 2
        $obj->setRejectedReason("_too_expensive");
        $this->assertEquals("too_expensive", $obj->getRejectedReason());
        $obj->setRejectedReason("_too_LONG_delays");
        $this->assertEquals("too_long_delays", $obj->getRejectedReason());
        $obj->setRejectedReason("outside_our_SKILL_sphere");
        $this->assertEquals("outside_our_skill_sphere", $obj->getRejectedReason());
        $obj->setRejectedReason("_outside_our_skill_sphere");
        $this->assertEquals("outside_our_skill_sphere", $obj->getRejectedReason());
        $obj->setRejectedReason("_non_prioritary_rad");
        $this->assertEquals("non_prioritary_rad", $obj->getRejectedReason());
        $obj->setRejectedReason("_incompatible_deadline");
        $this->assertEquals("incompatible_deadline", $obj->getRejectedReason());
        $obj->setRejectedReason("_too_many_samples");
        $this->assertEquals("too_many_samples", $obj->getRejectedReason());
        $obj->setRejectedReason("_transfered_to_privilegied_partner");
        $this->assertEquals("transfered_to_privilegied_mth_partner", $obj->getRejectedReason());
        $obj->setRejectedReason("_not_funded");
        $this->assertEquals("not_funded", $obj->getRejectedReason());
        $obj->setRejectedReason("_saved_twice");
        $this->assertEquals("saved_twice", $obj->getRejectedReason());

        // reset 2
        $obj->setRejectedReason("toto");
        $this->assertEquals("", $obj->getRejectedReason());
    }

    public function testGetSetDialogBoxVal()
    {
        $t = new Project("junit-test", null);
        $obj = new ProjectExtraData($t);
        // init
        $this->assertEquals("", $obj->getDialogBoxVal());

        // getters / setters 1
        $obj->setDialogBoxVal(ProjectExtraData::$DIALOG_BOX__REQUEST_NOT_CHECKED);
        $this->assertEquals("request_not_checked", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal(ProjectExtraData::$DIALOG_BOX__REQUEST_CHECKED_BY_MTH_STAFF);
        $this->assertEquals("request_checked_by_mth_staff", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal(ProjectExtraData::$DIALOG_BOX__APPOINTMENT_WITH_CLIENT_SCHEDULED);
        $this->assertEquals("appointment_with_client_scheduled", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal(ProjectExtraData::$DIALOG_BOX__REQUEST_UPDATED_AFTER_CLIENT_APPOINTMENT);
        $this->assertEquals("request_updated_after_client_appointment", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal(ProjectExtraData::$DIALOG_BOX__REQUEST_CHECKED_READY_TO_BE_PROCESSED);
        $this->assertEquals("request_checked_ready_to_be_processed", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal(ProjectExtraData::$DIALOG_BOX__SEARCHING_A_PF_ABLE_TO_PROCESS_THE_REQUEST);
        $this->assertEquals("searching_a_pf_able_to_process_the_request", $obj->getDialogBoxVal());

        // reset 1
        $obj->setDialogBoxVal(- 1);
        $this->assertEquals("", $obj->getDialogBoxVal());

        // getters / setters 2
        $obj->setDialogBoxVal("request_NOT_checked");
        $this->assertEquals("request_not_checked", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal("request_checked_BY_mth_staff");
        $this->assertEquals("request_checked_by_mth_staff", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal("_appointment_with_client_scheduled");
        $this->assertEquals("appointment_with_client_scheduled", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal("request_updated_after_client_appointment");
        $this->assertEquals("request_updated_after_client_appointment", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal("request_checked_ready_to_be_processed");
        $this->assertEquals("request_checked_ready_to_be_processed", $obj->getDialogBoxVal());
        $obj->setDialogBoxVal("_searching_a_PF_able_to_process_the_request");
        $this->assertEquals("searching_a_pf_able_to_process_the_request", $obj->getDialogBoxVal());

        // reset 2
        $obj->setDialogBoxVal("toto");
        $this->assertEquals("", $obj->getDialogBoxVal());
    }

    public function testPrune()
    {
        $t = new Project("junit-test", null);
        $obj = new ProjectExtraData($t);

        // init
        $this->assertNull($obj->getStoppedReason());
        $this->assertNull($obj->getDialogBoxTxt());

        $objBis1 = $obj->cleanPrivateData();
        $this->assertEquals("", $objBis1->getStoppedReason());
        $this->assertEquals("", $objBis1->getDialogBoxTxt());

        $obj->prune();

        // getters / setters 1
        $obj->setStoppedReason("hello world");
        $obj->setDialogBoxTxt("lorem ipsum");

        // bis
        $obj->prune();

        $this->assertNotNull($obj->getStoppedReason());
        $this->assertNotNull($obj->getDialogBoxTxt());

        $objBis2 = $obj->cleanPrivateData();
        $this->assertEquals("hello world", $objBis2->getStoppedReason());
        $this->assertEquals("lorem ipsum", $objBis2->getDialogBoxTxt());
    }
}
