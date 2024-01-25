<?php
require_once "bootstrap.tests.php";

// core test
require_once "../api/services/gdprManagementService.php";

// also needed for this test
require_once "../data-model/User.class.php";
/**
 * Test GDPR manager;
 *
 * @author Nils Paulhe
 *        
 */
class Data_model_userClassTest extends PHPUnit\Framework\TestCase
{


    public function testAnonymize()
    {
        // init
        $user = new User("login", "email", User::$STATUS_NOT_VALIDATED, User::$RIGHT_USER);
        $user->setFirstName("first");
        $user->setLastName("last");
        // test before
        $this->assertEquals(false, $user->isAnonymized(), "[error] isAnonymized() return not expected value");
        $this->assertEquals("email", $user->getEmail(), "[error] getEmail() return not expected value");
        $this->assertEquals("login", $user->getLogin(), "[error] getLogin() return not expected value");
        $this->assertEquals("first", $user->getFirstName(), "[error] getEmail() return not expected value");
        $this->assertEquals("last", $user->getLastName(), "[error] getLogin() return not expected value");
        // run method
        $this->assertEquals(true, $user->anonymize(), "[error] anonymize() return not expected value");
        // test after
        $this->assertEquals(true, $user->isAnonymized(), "[error] isAnonymized() return not expected value");
        $this->assertNotEquals("email", $user->getEmail(), "[error] getEmail() return not expected value");
        $this->assertNotEquals("login", $user->getLogin(), "[error] getLogin() return not expected value");
        $this->assertNotEquals("first", $user->getFirstName(), "[error] getEmail() return not expected value");
        $this->assertNotEquals("last", $user->getLastName(), "[error] getLogin() return not expected value");

        $this->assertEquals("******", $user->getPhoneNumber(), "[error] getLogin() return not expected value");
        $this->assertEquals("******", $user->getWorkplaceAddress(), "[error] getLogin() return not expected value");

        $this->assertEquals(32, strlen($user->getEmail()), "[error] wrong md5 size");
        $this->assertEquals(32, strlen($user->getLogin()), "[error] wrong md5 size");
        $this->assertEquals(32, strlen($user->getFirstName()), "[error] wrong md5 size");
        $this->assertEquals(32, strlen($user->getLastName()), "[error] wrong md5 size");
        // test bis
        $this->assertEquals(false, $user->anonymize(), "[error] anonymize() return not expected value");
    }


}