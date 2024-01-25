<?php
require_once "bootstrap.tests.php";
require_once "../api/services/emailManagementService.php";

// require_once "../data-model/User.class.php";
// require_once "../vendor/autoload.php";

/**
 * Test email sender;
 *
 * @author Nils Paulhe
 *        
 */
class EmailManagementServiceTest extends PHPUnit\Framework\TestCase
{

    /**
     * Try to send email
     */
    public function testSend()
    {
        // test send fail
        $success1 = EmailManagementService::sendEmailAccountCreation("nils.paulhe.FAIL@inra.fr", "Nils Paulhe", "en");
        $this->assertEquals(true, $success1, "[error] could not send an email to an INVALIDE address");

        // test send success
        $success2 = EmailManagementService::sendEmailAccountCreation("nils.paulhe@inrae.fr", "Nils Paulhe", "en");
        $this->assertEquals(true, $success2, "[error] could not send an email to a VALIDE address");
    }
}
