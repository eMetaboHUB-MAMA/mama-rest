<?php
require_once "bootstrap.tests.php";
require_once "../api/services/userManagementService.php";
require_once "../api/services/projectManagementService.php";
require_once "../api/services/eventManagementService.php";
require_once "../api/services/messageManagementService.php";
//
require_once "../email_jobs/specialEventMailler.php";


// require_once "../data-model/User.class.php";
// require_once "../vendor/autoload.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class MessagesManagementServiceTest extends PHPUnit\Framework\TestCase
{

    /**
     */
    public function testCreated()
    {

        // create user
        $email = "junit.test.create." . time() . "@inra.fr";
        $idUser = UserManagementService::create($email, $email, "nopTesta", "junit", "test", 44, "01 23 45 67 89", "labo of unit tests", "office truc street much", "public");
        $user = UserManagementService::get($idUser);

        // create
        // What would you like to do with MTH?
        $mthStuff = "create MAMA REST API UNIT TEST, just because love Chuck Testa";
        // type of demand
        $demand_type_eq = false;
        $demand_type_labRout = true;
        $demand_type_feasibility = false;
        $demand_type_formation = false;
        $demand_type_data_processing = false;
        $demand_type_other = false;
        // number of sample
        $demand_sample_nb = "50 or fewer";
        // thematic cloud word
        $cloudWords = null;
        $subCloudWords = null;
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
        $financialContextIsProjectANR = false;
        $financialContextIsProjectNational = false;
        $financialContextIsProjectRegional = false;
        $financialContextIsProjectCompagnyTutorship = false;
        $financialContextIsProjectOwnResourcesLaboratory = false;
        $financialContextIsProjectInternationalOutsideEU = false;
        $financialContextIsProjectOther = false;
        // Other Financial
        $financialContextIsProjectOtherValue = "rainbows and unicorns";

        // mama#60
        $labRNSR = "jean-PFEM ~ mama#60";

        // create
        $title = "junit.test.create." . time() . " TITLE";
        $idProject = ProjectManagementService::create( //
            $title, //
            $user,
            $mthStuff,
            $demand_type_eq,
            $demand_type_labRout,
            $demand_type_feasibility,
            $demand_type_formation,
            $demand_type_data_processing,
            $demand_type_other,
            $demand_sample_nb,
            $cloudWords,
            $subCloudWords,
            $targeted,
            $mthPF,
            $forwardAR2copartner,
            $scientificContext,
            $scientificContextFile,
            $financialContextIsProjectFinanced,
            $financialContextIsProjectInProvisioning,
            $financialContextIsProjectOnOwnSupply,
            $financialContextIsProjectNotFinanced,
            $financialContextIsProjectEU,
            $financialContextIsProjectANR,
            $financialContextIsProjectNational,
            $financialContextIsProjectRegional,
            $financialContextIsProjectCompagnyTutorship,
            $financialContextIsProjectOwnResourcesLaboratory,
            $financialContextIsProjectInternationalOutsideEU,
            $financialContextIsProjectOther,
            $financialContextIsProjectOtherValue,
            $labRNSR
        );

        // get to add message
        $newProject = ProjectManagementService::get($idProject);

        // create message
        $message = "junit.test.create." . time() . " MESSAGE";
        $idMessage = MessageManagementService::create($message, $user, null, null, $newProject);

        // check 1
        $newMessage = MessageManagementService::get($idMessage);
        $this->assertEquals($message, $newMessage->getMessage(), "[error] 'create' or 'get' (by id) does not work");
        $this->assertEquals($user->getId(), $newMessage->getFromUser()
            ->getId(), "[error] 'create' or 'get' (by id) does not work");
        $this->assertEquals($newProject->getId(), $newMessage->getToProject()
            ->getId(), "[error] 'create' or 'get' (by id) does not work");

        // check 2 with Search
        $newMessage2 = MessageManagementService::search($message);
        $messageS = $newMessage2[0];
        $this->assertEquals($idMessage, $messageS->getId(), "[error] 'create' or 'get' (by id) does not work");
    }
}
