<?php
require_once "bootstrap.tests.php";

// core test
require_once "../api/services/gdprManagementService.php";

// also needed for this test
require_once "../data-model/ProjectEvent.class.php";
require_once "../data-model/UserEvent.class.php";
require_once "../api/services/userManagementService.php";
require_once "../api/services/eventManagementService.php";
require_once "../api/services/projectManagementService.php";

/**
 * Test GDPR manager;
 *
 * @author Nils Paulhe
 *        
 */
class GdprManagementServiceTest extends PHPUnit\Framework\TestCase
{


    public function testGetUsersThatCanBeAnonymized()
    {

        // for unit test: init database connection
        $configFile = __DIR__ . "/mama-test.ini";
        $ini_array = parse_ini_file($configFile, true);
        $mysqli = new mysqli($ini_array['database']['host'], $ini_array['database']['user'], $ini_array['database']['password'], $ini_array['database']['dbname']);
        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit(1);
        }

        // ====================================================================
        // insert users that can be annonymised 
        // ====================================================================

        // ====================================================================
        //(no related project, inactived, not anonymized, no activity for more than 5 years)
        $email11 = "junit.test.getUsersThatCanBeAnonymized.11." . time() . "@inrae.fr";
        $old1 = (new \DateTime("-61 month"));
        $idUser11 = UserManagementService::create($email11, $email11, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        if (
            $mysqli->query("UPDATE users SET 
            last_activity = '" . $old1->format('Y-m-d H:i:s') . "',
            user_status = '" . User::$STATUS_INACTIVE . "'
            WHERE id= $idUser11 ; ") !== TRUE
        ) {
            printf("[error] could not update user 11 manualy");
            exit(1);
        }

        // ====================================================================
        // (related project but archived, inactived, not anonymized, no activity for more than 5 years)
        $email12 = "junit.test.getUsersThatCanBeAnonymized.12." . time() . "@inrae.fr";
        $idUser12 = UserManagementService::create($email12, $email12, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        if (
            $mysqli->query("UPDATE users SET 
            last_activity = '" . $old1->format('Y-m-d H:i:s') . "',
            user_status = '" . User::$STATUS_INACTIVE . "'
            WHERE id= $idUser12 ; ") !== TRUE
        ) {
            printf("[error] could not update user 12 manualy");
            exit(1);
        }
        $user12 = UserManagementService::get($idUser12);
        $idProject1 = ProjectManagementService::create(
            //
            "junit.test.getUsersThatCanBeAnonymized.1." . microtime() . " TITLE",
            $user12,
            "create MAMA REST API UNIT TEST, just because love Chuck Testa",
            false,
            false,
            false,
            false,
            false,
            false,
            "50 or fewer",
            null,
            null,
            false,
            null,
            false,
            "pancakes, i need pancakes!!!",
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            "rainbows and unicorns",
            "lab RNSR"
        );
        $project1 = ProjectManagementService::get($idProject1);
        $project1->setStatus("archived"); // rejected
        ProjectManagementService::updateObject($project1, true, $user12);

        // ====================================================================
        // insert users that can't be annonymised
        // ====================================================================

        // ====================================================================
        // (related project - OWNER, inactived, not anonymized, no activity for more than 5 years)
        $email21 = "junit.test.getUsersThatCanBeAnonymized.21." . time() . "@inrae.fr";
        $idUser21 = UserManagementService::create($email21, $email21, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        if (
            $mysqli->query("UPDATE users SET 
            last_activity = '" . $old1->format('Y-m-d H:i:s') . "',
            user_status = '" . User::$STATUS_INACTIVE . "'
            WHERE id= $idUser21; ") !== TRUE
        ) {
            printf("[error] could not update user 21 manualy");
            exit(1);
        }
        $user21 = UserManagementService::get($idUser21);
        $idProject2 = ProjectManagementService::create(
            //
            "junit.test.getUsersThatCanBeAnonymized.2." . microtime() . " TITLE",
            $user21,
            "create MAMA REST API UNIT TEST, just because love Chuck Testa",
            false,
            false,
            false,
            false,
            false,
            false,
            "50 or fewer",
            null,
            null,
            false,
            null,
            false,
            "pancakes, i need pancakes!!!",
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            "rainbows and unicorns",
            "lab RNSR"
        );

        // ====================================================================
        // (related project - MANAGER, inactived, not anonymized, no activity for more than 5 years)
        $email22 = "junit.test.getUsersThatCanBeAnonymized.22." . time() . "@inrae.fr";
        $idUser22 = UserManagementService::create($email22, $email22, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        if (
            $mysqli->query("UPDATE users SET 
            last_activity = '" . $old1->format('Y-m-d H:i:s') . "',
            user_status = '" . User::$STATUS_INACTIVE . "'
            WHERE id= $idUser22 ; ") !== TRUE
        ) {
            printf("[error] could not update user 22 manualy");
            exit(1);
        }
        if ($mysqli->query("UPDATE projects SET analyst_in_charge_id = $idUser22 WHERE id= $idProject2 ; ") !== TRUE) {
            printf("[error] could not update project 2 manualy");
            exit(1);
        }

        // ====================================================================
        // (related project - ANALYSTS, inactived, not anonymized, no activity for more than 5 years)
        // NOTE: user pass - can't bypass on join table
        $email23 = "junit.test.getUsersThatCanBeAnonymized.23." . time() . "@inrae.fr";
        $idUser23 = UserManagementService::create($email23, $email23, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        if (
            $mysqli->query("UPDATE users SET 
            last_activity = '" . $old1->format('Y-m-d H:i:s') . "',
            user_status = '" . User::$STATUS_INACTIVE . "'
            WHERE id= $idUser23 ; ") !== TRUE
        ) {
            printf("[error] could not update user 23 manualy");
            exit(1);
        }
        if ($mysqli->query("INSERT INTO users_involved_in_projects (user_id, project_id) VALUES ($idUser23, $idProject2); ") !== TRUE) {
            printf("[error] could not insert users_involved_in_projects manualy");
            exit(1);
        }

        // ====================================================================
        // (no related project, NOT INACTIVED, not anonymized, no activity for more than 5 years)
        $email24 = "junit.test.getUsersThatCanBeAnonymized.24." . time() . "@inrae.fr";
        $idUser24 = UserManagementService::create($email24, $email24, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        if (
            $mysqli->query("UPDATE users SET 
            last_activity = '" . $old1->format('Y-m-d H:i:s') . "',
            user_status = '" . User::$STATUS_ACTIVE . "'
            WHERE id= $idUser24 ; ") !== TRUE
        ) {
            printf("[error] could not update user 24 manualy");
            exit(1);
        }

        // ====================================================================
        // (no related project, inactived, ANONYMIZED, no activity for more than 5 years)
        $email25 = "junit.test.getUsersThatCanBeAnonymized.25." . time() . "@inrae.fr";
        $idUser25 = UserManagementService::create($email25, $email25, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        if (
            $mysqli->query("UPDATE users SET 
            last_activity = '" . $old1->format('Y-m-d H:i:s') . "',
            user_status = '" . User::$STATUS_INACTIVE . "',
            anonymized = true
            WHERE id= $idUser25 ; ") !== TRUE
        ) {
            printf("[error] could not update user 25 manualy");
            exit(1);
        }

        // ====================================================================
        // (no related project, inactived, not anonymized, ACTIVITY for more than 5 years)
        $email26 = "junit.test.getUsersThatCanBeAnonymized.26." . time() . "@inrae.fr";
        $idUser26 = UserManagementService::create($email26, $email26, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $old2 = (new \DateTime("-59 month"));
        if (
            $mysqli->query("UPDATE users SET 
            last_activity = '" . $old2->format('Y-m-d H:i:s') . "',
            user_status = '" . User::$STATUS_INACTIVE . "'
            WHERE id= $idUser26 ; ") !== TRUE
        ) {
            printf("[error] could not update user 26 manualy");
            exit(1);
        }

        // ====================================================================
        // run method to test
        $users = GdprManagementService::getUsersThatCanBeAnonymized();
        $nbUsers = count($users);

        // check results
        $this->assertEquals(3, $nbUsers, "[error] getUsersThatCanBeAnonymized() return empty list or more users than expected");
        $this->assertEquals($idUser11, $users[0]->getId(), "[error] getUsersThatCanBeAnonymized() didn't returned expected user");
        $this->assertEquals($idUser12, $users[1]->getId(), "[error] getUsersThatCanBeAnonymized() didn't returned expected user");

    }


    public function testCanUserBeAnonymized()
    {
        // for unit test: init database connection
        $configFile = __DIR__ . "/mama-test.ini";
        $ini_array = parse_ini_file($configFile, true);
        $mysqli = new mysqli($ini_array['database']['host'], $ini_array['database']['user'], $ini_array['database']['password'], $ini_array['database']['dbname']);
        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit(1);
        }

        // ====================================================================
        // insert users that can be annonymised 
        // ====================================================================

        //---------------------------------------------------------------------
        //(no related project, not anonymized)
        $email11 = "junit.test.testCanUserBeAnonymized.11." . time() . "@inrae.fr";
        $idUser11 = UserManagementService::create($email11, $email11, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");

        $testU11 = GdprManagementService::canUserBeAnonymized($idUser11);
        $this->assertEquals("_YES", $testU11, "[error] testCanUserBeAnonymized() returned unexpected value");

        // related project -owner-, but rejected
        $email12 = "junit.test.testCanUserBeAnonymized.12." . time() . "@inrae.fr";
        $idUser12 = UserManagementService::create($email12, $email12, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $user12 = UserManagementService::get($idUser12);
        $idProject1 = ProjectManagementService::create(
            //
            "junit.test.testCanUserBeAnonymized.1." . microtime() . " TITLE",
            $user12,
            "create MAMA REST API UNIT TEST, just because love Chuck Testa",
            false,
            false,
            false,
            false,
            false,
            false,
            "50 or fewer",
            null,
            null,
            false,
            null,
            false,
            "pancakes, i need pancakes!!!",
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            "rainbows and unicorns",
            "lab RNSR"
        );
        $project1 = ProjectManagementService::get($idProject1);
        $project1->setStatus("rejected"); // archived
        ProjectManagementService::updateObject($project1, true, $user12);
        if ($mysqli->query("UPDATE projects SET project_status = " . Project::$AD_STATUS_REJECTED . " WHERE id= $idProject1 ; ") !== TRUE) {
            printf("[error] could not update project 1 manualy");
            exit(1);
        }

        // test
        $testU12 = GdprManagementService::canUserBeAnonymized($idUser12);
        $this->assertEquals("_YES", $testU12, "[error] testCanUserBeAnonymized() returned unexpected value");

        //---------------------------------------------------------------------
        // related project -manager-, but archived
        $email13 = "junit.test.testCanUserBeAnonymized.13." . time() . "@inrae.fr";
        $idUser13 = UserManagementService::create($email13, $email13, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $user13 = UserManagementService::get($idUser13);
        $project1 = ProjectManagementService::get($idProject1);
        $project1->setAnalystInCharge($user13);
        $project1->setStatus("archived"); // rejected
        ProjectManagementService::updateObject($project1, true, $user13);

        // test
        $testU13 = GdprManagementService::canUserBeAnonymized($idUser13);
        $this->assertEquals("_YES", $testU13, "[error] testCanUserBeAnonymized() returned unexpected value");

        //---------------------------------------------------------------------
        // related project -involved-, but archived
        $email14 = "junit.test.testCanUserBeAnonymized.14." . time() . "@inrae.fr";
        $idUser14 = UserManagementService::create($email14, $email14, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $user14 = UserManagementService::get($idUser14);
        $project1 = ProjectManagementService::get($idProject1);
        $project1->setAnalystsInvolved([$user14]);
        $project1->setStatus("archived"); // rejected
        ProjectManagementService::updateObject($project1, true, $user14);

        // test
        $testU14 = GdprManagementService::canUserBeAnonymized($idUser14);
        $this->assertEquals("_YES", $testU14, "[error] testCanUserBeAnonymized() returned unexpected value");

        // ====================================================================
        // insert users that can't be annonymised
        // ====================================================================

        //---------------------------------------------------------------------
        // null user
        // test
        $testU20 = GdprManagementService::canUserBeAnonymized(-1);
        $this->assertEquals("_NO__user_not_found", $testU20, "[error] testCanUserBeAnonymized() returned unexpected value");

        //---------------------------------------------------------------------
        // already anno

        $email21 = "junit.test.testCanUserBeAnonymized.21." . time() . "@inrae.fr";
        $idUser21 = UserManagementService::create($email21, $email21, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $user21 = UserManagementService::get($idUser21);
        $user21->anonymize();
        UserManagementService::updateObject($user21, true, $user21);

        // test
        $testU21 = GdprManagementService::canUserBeAnonymized($idUser21);
        $this->assertEquals("_NO__user_already_anonymized", $testU21, "[error] testCanUserBeAnonymized() returned unexpected value");

        //---------------------------------------------------------------------
        // owner of active project

        $email22 = "junit.test.testCanUserBeAnonymized.22." . time() . "@inrae.fr";
        $idUser22 = UserManagementService::create($email22, $email22, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $user22 = UserManagementService::get($idUser22);
        $idProject2 = ProjectManagementService::create(
            //
            "junit.test.testCanUserBeAnonymized.22." . microtime() . " TITLE",
            $user22,
            "create MAMA REST API UNIT TEST, just because love Chuck Testa",
            false,
            false,
            false,
            false,
            false,
            false,
            "50 or fewer",
            null,
            null,
            false,
            null,
            false,
            "pancakes, i need pancakes!!!",
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            "rainbows and unicorns",
            "lab RNSR"
        );
        $project2 = ProjectManagementService::get($idProject2);
        $project2->setStatus("accepted"); // neither rejected/archived
        ProjectManagementService::updateObject($project2, true, $user22);

        // test
        $testU22 = GdprManagementService::canUserBeAnonymized($idUser22);
        $this->assertEquals("_NO__user_is_owner_of_active_projects", $testU22, "[error] testCanUserBeAnonymized() returned unexpected value");

        //---------------------------------------------------------------------
        // manager of active project
        $email23 = "junit.test.testCanUserBeAnonymized.23." . time() . "@inrae.fr";
        $idUser23 = UserManagementService::create($email23, $email23, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $user23 = UserManagementService::get($idUser23);
        $project2 = ProjectManagementService::get($idProject2);
        $project2->setAnalystInCharge($user23);
        ProjectManagementService::updateObject($project2, true, $user23);
        if ($mysqli->query("UPDATE projects SET analyst_in_charge_id = " . $idUser23 . " WHERE id= $idProject2 ; ") !== TRUE) {
            printf("[error] could not update project 2 manualy");
            exit(1);
        }

        // test
        $testU23 = GdprManagementService::canUserBeAnonymized($idUser23);
        $this->assertEquals("_NO__user_is_inCharge_of_active_projects", $testU23, "[error] testCanUserBeAnonymized() returned unexpected value");

        //---------------------------------------------------------------------
        // involved in active project
        $email24 = "junit.test.testCanUserBeAnonymized.24." . time() . "@inrae.fr";
        $idUser24 = UserManagementService::create($email24, $email24, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $user24 = UserManagementService::get($idUser24);
        $project2 = ProjectManagementService::get($idProject2);
        $project2->setAnalystInCharge($user24);
        ProjectManagementService::updateObject($project2, true, $user23);
        if ($mysqli->query("INSERT INTO users_involved_in_projects (user_id, project_id) VALUES ($idUser24, $idProject2); ") !== TRUE) {
            printf("[error] could not insert users_involved_in_projects manualy");
            exit(1);
        }

        // test
        $testU24 = GdprManagementService::canUserBeAnonymized($idUser24);
        $this->assertEquals("_NO__user_is_involved_in_active_projects", $testU24, "[error] testCanUserBeAnonymized() returned unexpected value");

    }

    public function testAnonymizeUser()
    {

        // for unit test: init database connection
        $configFile = __DIR__ . "/mama-test.ini";
        $ini_array = parse_ini_file($configFile, true);
        $mysqli = new mysqli($ini_array['database']['host'], $ini_array['database']['user'], $ini_array['database']['password'], $ini_array['database']['dbname']);
        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit(1);
        }

        // ====================================================================
        // insert users that can be annonymised 
        // ==================================================================== 

        $email11 = "junit.test.testAnonymizeUser.11." . time() . "@inrae.fr";
        $idUser11 = UserManagementService::create($email11, $email11, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");

        // action
        $testU11 = GdprManagementService::anonymizeUser($idUser11);

        // tests
        $user1 = UserManagementService::get($idUser11);
        $this->assertEquals(true, $testU11, "[error] testAnonymizeUser() returned unexpected value");
        $this->assertNotEquals($email11, $user1->getLogin(), "[error] getLogin() return not expected value");
        $this->assertNotEquals($email11, $user1->getEmail(), "[error] getEmail() return not expected value");
        $this->assertNotEquals("junit", $user1->getFirstName(), "[error] getFirstName() return not expected value");
        $this->assertNotEquals("test", $user1->getLastName(), "[error] getLastName() return not expected value");

        // ====================================================================
        // insert users that can't be annonymised 
        // ==================================================================== 

        $email12 = "junit.test.testAnonymizeUser.12." . time() . "@inrae.fr";
        $idUser12 = UserManagementService::create($email12, $email12, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        // if ($mysqli->query("UPDATE users SET anonymized = true WHERE id= $idUser12 ; ") !== TRUE) {
        //     printf("[error] could not update user 12 manualy");
        //     exit(1);
        // }
        $user12 = UserManagementService::get($idUser12);
        $idProject1 = ProjectManagementService::create(
            //
            "junit.test.testAnonymizeUser.1." . microtime() . " TITLE",
            $user12,
            "create MAMA REST API UNIT TEST, just because love Chuck Testa",
            false,
            false,
            false,
            false,
            false,
            false,
            "50 or fewer",
            null,
            null,
            false,
            null,
            false,
            "pancakes, i need pancakes!!!",
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            "rainbows and unicorns",
            "lab RNSR"
        );

        // action
        $testU12 = GdprManagementService::anonymizeUser($idUser12);

        // tests
        $user2 = UserManagementService::get($idUser12);
        $this->assertEquals(false, $testU12, "[error] testAnonymizeUser() returned unexpected value");
        $this->assertEquals($email12, $user2->getLogin(), "[error] getLogin() return not expected value");
        $this->assertEquals($email12, $user2->getEmail(), "[error] getEmail() return not expected value");
        $this->assertEquals("junit", $user2->getFirstName(), "[error] getFirstName() return not expected value");
        $this->assertEquals("test", $user2->getLastName(), "[error] getLastName() return not expected value");
    }
}