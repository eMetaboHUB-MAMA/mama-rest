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
class CronServiceTest extends PHPUnit\Framework\TestCase
{

    /**
     */
    public function testMonthlyUsersInactiver()
    {
        // for unit test: init database connection
        $configFile = __DIR__ . "/mama-test.ini";
        $ini_array = parse_ini_file ( $configFile, true );
        $mysqli = new mysqli($ini_array ['database']['host'], $ini_array ['database']['user'], $ini_array ['database']['password'] , $ini_array ['database']['dbname']);
        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit(1);
        }
    
        // create users
        $email1 = "junit.test.create." . microtime() . "@inra.fr";
        $idUser1 = UserManagementService::create($email1, $email1, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $userNotVal1 = UserManagementService::get($idUser1);
        
        $email2 = "junit.test.create." . microtime() . "@inrae.fr";
        $idUser2 = UserManagementService::create($email2, $email2, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $userVal2 = UserManagementService::get($idUser2);
        $userVal2->setStatus(User::$STATUS_ACTIVE);
        UserManagementService::updateObject ( $userVal2, true );
        $userVal2 = UserManagementService::get($idUser2);
        
        $email3 = "junit.test.create." . microtime() . "@inra.fr";
        $idUser3 = UserManagementService::create($email3, $email3, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $userVal3 = UserManagementService::get($idUser3);
        $userVal3->setStatus(User::$STATUS_ACTIVE);
        UserManagementService::updateObject ( $userVal3, true );
        $userVal3 = UserManagementService::get($idUser3);
        
        // UPDATE USERS BEFORE
        $old1 = (new \DateTime ( "-25 month" ));
        $old2 = (new \DateTime ( "-23 month" ));
        if ($mysqli->query("UPDATE users SET last_activity = '".$old1->format('Y-m-d H:i:s') . "' WHERE id= $idUser1 ; ") !== TRUE) {
            printf("[error] could not update user 1 manualy");
            exit(1);
        }
        if ($mysqli->query("UPDATE users SET last_activity = '". $old1->format('Y-m-d H:i:s') . "' WHERE id= $idUser2 ; ") !== TRUE) {
            printf("[error] could not update user 2 manualy");
            exit(1);
        }
        if ($mysqli->query("UPDATE users SET last_activity = '". $old2->format('Y-m-d H:i:s') . "' WHERE id= $idUser3 ; ") !== TRUE) {
            printf("[error] could not update user 3 manualy");
            exit(1);
        }
        
        // CHECK USERS BEFORE
        $this->assertEquals("not_validated", $userNotVal1->getStatus(), "[error] get user 1 status 'not_validated'; ");
        $this->assertEquals("active", $userVal2->getStatus(), "[error] get user 2 status 'active'; ");
        $this->assertEquals("active", $userVal3->getStatus(), "[error] get user 3 status 'active'; ");
        
        /////////////////////////////////////////////////////////////////////////////////////
        // RUN CRON CORE
        $date = new \DateTime ( "-2 year" );
        $users = UserManagementService::fetchNotActiveUsers ( $date );
        // for each user, inactive them
        foreach ( $users as $k => $user ) {
            echo "[" . date ( "Y-m-d H:i:s" ) . "] user '" . $user->getLogin () . "' not active the last 2 years. \n";
            UserManagementService::setToInactive ( $user );
        }
        /////////////////////////////////////////////////////////////////////////////////////
        
        // update users after
        $userNotVal1 = UserManagementService::get($idUser1);
        $userVal2 = UserManagementService::get($idUser2);
        $userVal3 = UserManagementService::get($idUser3);
        
        // CHECK USERS AFTER
        $this->assertEquals("not_validated", $userNotVal1->getStatus(), "[error] get user 1 status 'inactive'; ");
        $this->assertEquals("inactive", $userVal2->getStatus(), "[error] get user 2 status 'inactive'; ");
        $this->assertEquals("active", $userVal3->getStatus(), "[error] get user 3 status 'active'; ");
        
    }

}
