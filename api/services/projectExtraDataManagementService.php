<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA PORJECTS FUNCTIONS
require_once __DIR__ . "/../../vendor/autoload.php";
// Data Model
// require_once __DIR__ . "/../../data-model/User.class.php";
require_once __DIR__ . "/../../data-model/Project.class.php";
require_once __DIR__ . "/../../data-model/ProjectExtraData.class.php";

// require_once __DIR__ . "/../../data-model/Message.class.php";
// require_once __DIR__ . "/../../data-model/ThematicCloudWord.class.php";
// require_once __DIR__ . "/../../data-model/MTHplatform.class.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class ProjectExtraDataManagementService
{

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // GET ALL
    /**
     * Get all projects (or filter with $_GET fields)
     *
     * @return List of Project(s)
     */
    public static function getAll($user = null, $userFilter = null)
    {
        return null;
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // COUNT

    /**
     *
     * @param unknown $user
     * @param unknown $filterStatus
     * @return unknown
     */
    public static function count($user = null, $filterStatus = null, $userFilter = null)
    {
        return null;
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // GET ONE

    /**
     *
     * @param long $id
     * @return Project
     */
    public static function get($id)
    {
        // init
        $entityManager = $GLOBALS['entityManager'];
        $projectExtraData = $entityManager->getRepository('ProjectExtraData')->find($id);
        return $projectExtraData;
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // CREATE
    public static function create($analysisRequest)
    {

        // init
        $entityManager = $GLOBALS['entityManager'];

        // create and save in the database
        $projectExtraData = new ProjectExtraData($analysisRequest);

        // ALL DATUM
        // do it or not?

        // save
        $entityManager->persist($projectExtraData);
        $entityManager->flush();

        // // create new Event
        // EventManagementService::createProjectEvent ( $owner, ProjectEvent::$EVENT_TYPE_UPDATE_PROJECT__new_extra_data, $projectExtraData );

        return $projectExtraData->getId();
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // UPDATE
    public static function update($id, 
			/* -- */
			/* -- */
			$userSource = null)
    {

        // init
        $entityManager = $GLOBALS['entityManager'];

        // get project from the database
        $projectExtraData = null;
        try {
            $projectExtraData = ProjectExtraDataManagementService::get($id);
        } catch (Exception $em) {
            // no entity
            return false;
        }
        if (is_null($projectExtraData) || $projectExtraData->isDeleted()) {
            return false;
        }

        // $project->setTitle ( $title );
        // $project->setInterestInMthCollaboration ( $interestInMthCollaboration );
        // $project->setFinancialContextIsProjectOtherValue ( $financialContextIsProjectOtherValue );

        $projectExtraData->setUpdated();

        $entityManager->persist($projectExtraData);
        $entityManager->flush();

        // // create new Event
        // if ($userSource != null)
        // EventManagementService::createProjectEvent ( $userSource, UserEvent::$EVENT_TYPE_UPDATE_PROJECT__informations, $projectExtraData );

        return true;
    }

    /**
     *
     * @param unknown $project
     */
    public static function updateObject($projectExtraData, $isAdmin = false, $userSource = null)
    {
        if (is_null($projectExtraData) || ! $isAdmin)
            return false;

        $projectExtraDataInDB = null;
        $updated = false;
        $updateDialogBoxData = false;
        $entityManager = $GLOBALS['entityManager'];

        if (is_a($projectExtraData, "ProjectExtraData")) {
            $projectExtraDataInDB = ProjectExtraDataManagementService::get($projectExtraData->getId());

            // $projectInDB->setTitle ( $project->getTitle () );

            // other ($updateBasicData = true)
            // checkbox
            if ($projectExtraDataInDB->getKnowMTHviaCoworkerOrFriend() != $projectExtraData->getKnowMTHviaCoworkerOrFriend()) {
                $projectExtraDataInDB->setKnowMTHviaCoworkerOrFriend($projectExtraData->getKnowMTHviaCoworkerOrFriend());
                $updated = true;
            }
            if ($projectExtraDataInDB->getKnowMTHviaPublication() != $projectExtraData->getKnowMTHviaPublication()) {
                $projectExtraDataInDB->setKnowMTHviaPublication($projectExtraData->getKnowMTHviaPublication());
                $updated = true;
            }
            if ($projectExtraDataInDB->getKnowMTHviaWebsite() != $projectExtraData->getKnowMTHviaWebsite()) {
                $projectExtraDataInDB->setKnowMTHviaWebsite($projectExtraData->getKnowMTHviaWebsite());
                $updated = true;
            }
            if ($projectExtraDataInDB->getKnowMTHviaSearchEngine() != $projectExtraData->getKnowMTHviaSearchEngine()) {
                $projectExtraDataInDB->setKnowMTHviaSearchEngine($projectExtraData->getKnowMTHviaSearchEngine());
                $updated = true;
            }
            // radio
            if ($projectExtraDataInDB->getLaboType() != $projectExtraData->getLaboType()) {
                $projectExtraDataInDB->setLaboType($projectExtraData->getLaboType());
                $updated = true;
            }
            // select
            if ($projectExtraDataInDB->getRejectedReason() != $projectExtraData->getRejectedReason()) {
                $projectExtraDataInDB->setRejectedReason($projectExtraData->getRejectedReason());
                $updated = true;
            }
            if ($projectExtraDataInDB->getBlockedReason() != $projectExtraData->getBlockedReason()) {
                $projectExtraDataInDB->setBlockedReason($projectExtraData->getBlockedReason());
                $updated = true;
            }
            // textarea
            if ($projectExtraDataInDB->getSyntheticUserNeeds() != $projectExtraData->getSyntheticUserNeeds()) {
                $projectExtraDataInDB->setSyntheticUserNeeds($projectExtraData->getSyntheticUserNeeds());
                $updated = true;
            }
            if ($projectExtraDataInDB->getStoppedReason() != $projectExtraData->getStoppedReason()) {
                $projectExtraDataInDB->setStoppedReason($projectExtraData->getStoppedReason());
                $updated = true;
            }
            // select single
            // if ($projectExtraDataInDB->getSamplesNumber () != $projectExtraData->getSamplesNumber ()) {
            // $projectExtraDataInDB->setSamplesNumber ( $projectExtraData->getSamplesNumber () );
            // $updateBasicData = true;
            // $updated = true;
            // }
            // select multi
            // if ($projectExtraDataInDB->getMthPlatforms () != $projectExtraData->getMthPlatforms ()) {
            // $projectExtraDataInDB->setMthPlatforms ( $projectExtraData->getMthPlatforms () );
            // $updateBasicData = true;
            // $updated = true;
            // }
            // text
            if ($projectExtraDataInDB->getAdministrativeContext() != $projectExtraData->getAdministrativeContext()) {
                $projectExtraDataInDB->setAdministrativeContext($projectExtraData->getAdministrativeContext());
                $updated = true;
            }
            if ($projectExtraDataInDB->getGeographicContext() != $projectExtraData->getGeographicContext()) {
                $projectExtraDataInDB->setGeographicContext($projectExtraData->getGeographicContext());
                $updated = true;
            }
            if ($projectExtraDataInDB->getProjectMaturity() != $projectExtraData->getProjectMaturity()) {
                $projectExtraDataInDB->setProjectMaturity($projectExtraData->getProjectMaturity());
                $updated = true;
            }
            if ($projectExtraDataInDB->getDeadline() != $projectExtraData->getDeadline()) {
                $projectExtraDataInDB->setDeadline($projectExtraData->getDeadline());
                $updated = true;
            }
            if ($projectExtraDataInDB->getBudgetConstraint() != $projectExtraData->getBudgetConstraint()) {
                $projectExtraDataInDB->setBudgetConstraint($projectExtraData->getBudgetConstraint());
                $updated = true;
            }
            // new 1.0.3
            if ($projectExtraDataInDB->getDialogBoxVal() != $projectExtraData->getDialogBoxVal()) {
                $projectExtraDataInDB->setDialogBoxVal($projectExtraData->getDialogBoxVal());
                $updateDialogBoxData = true;
                $updated = true;
            }
            if ($projectExtraDataInDB->getDialogBoxTxt() != $projectExtraData->getDialogBoxTxt()) {
                $projectExtraDataInDB->setDialogBoxTxt($projectExtraData->getDialogBoxTxt());
                $updateDialogBoxData = true;
                $updated = true;
            }
        } else { // case of json object
            $projectExtraDataInDB = ProjectExtraDataManagementService::get($projectExtraData['id']);

            // TODO update via JSON
        }

        // in db!!!
        if ($updated) {
            $projectExtraDataInDB->setUpdated();
            $entityManager->persist($projectExtraDataInDB);
            $entityManager->flush();
        }

        // // create new Event
        // if ($userSource != null && $projectExtraDataInDB && $updateDialogBoxData) {
        // EventManagementService::createProjectEvent($userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__dialog_box, $projectExtraDataInDB);
        // }

        return true;
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // DELETE
    /**
     *
     * @param unknown $id
     * @return boolean
     */
    public static function delete($id, $userSource = null)
    {

        // init
        $entityManager = $GLOBALS['entityManager'];

        // get project from the database
        $projectExtraData = ProjectExtraDataManagementService::get($id);

        if (is_null($projectExtraData) || $projectExtraData->isDeleted())
            return false;

        $projectExtraData->delete();

        $projectExtraData->setUpdated();

        $entityManager->persist($projectExtraData);
        $entityManager->flush();

        // create new Event
        // if ($userSource != null)
        // EventManagementService::createProjectEvent ( $userSource, UserEvent::$EVENT_TYPE_UPDATE_PROJECT__deleted, $projectExtraData );

        return true;
    }
    // //////////////////////////////////////////////////////////////////////////////////////////////
    // OTHER

    // ...
}