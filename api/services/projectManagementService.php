<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA PORJECTS FUNCTIONS
require_once __DIR__ . "/../../vendor/autoload.php";
// Data Model
require_once __DIR__ . "/../../data-model/User.class.php";
require_once __DIR__ . "/../../data-model/Project.class.php";
require_once __DIR__ . "/../../data-model/ProjectExtraData.class.php";
require_once __DIR__ . "/../../data-model/Message.class.php";
require_once __DIR__ . "/../../data-model/ThematicCloudWord.class.php";
require_once __DIR__ . "/../../data-model/SubThematicCloudWord.class.php";
require_once __DIR__ . "/../../data-model/MTHplatform.class.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class ProjectManagementService {
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ALL
	/**
	 * Get all projects (or filter with $_GET fields)
	 *
	 * @return List of Project(s)
	 */
	public static function getProjects($user = null, $userFilter = null) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// check filters
		$filter = false;
		$filterStart = false;
		$filterLimit = false;
		
		$where = "";
		$filterDeleted = false;
		$deleted = null;
		$join = "";
		
		$userID = null;
		if (! is_null ( $user )) {
			$filter = true;
			// construct where
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			if ($userFilter != null && $userFilter == "owner")
				$join .= " JOIN p.owner u  ";
			else if ($userFilter != null && $userFilter == "inCharge")
				$join .= " JOIN p.analystInCharge u  ";
			else if ($userFilter != null && $userFilter == "involved")
				$join .= " JOIN p.analystsInvolved u  ";
			else
				$join .= " JOIN p.owner u  ";
			$where .= " u.id = :userID ";
			$userID = $user;
		}
		
		if (isset ( $_GET ['start'] ) && $_GET ['start'] != "") { // && is_int ( $_GET ['start'] )
			$filter = true;
			$filterStart = true;
			$offset = intval ( $_GET ['start'] );
		}
		
		if (isset ( $_GET ['limit'] ) && $_GET ['limit'] != "") { // && is_int ( $_GET ['limit'] )
			$filter = true;
			$filterLimit = true;
			$maxResults = intval ( $_GET ['limit'] );
		}
		
		if (isset ( $_GET ['deleted'] ) && $_GET ['deleted'] != "") { // && is_int ( $_GET ['limit'] )
			$filter = true;
			$filterDeleted = true;
			
			// get deleted value
			if (is_bool ( $_GET ['deleted'] ))
				$deleted = boolval ( $_GET ['deleted'] );
			else {
				if (strtolower ( $_GET ['deleted'] ) == "true") {
					$deleted = true;
				} else {
					$deleted = false;
				}
			}
			
			// construct where
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " p.deleted = :deleted ";
		}
		
		if (isset ( $_GET ['status'] ) && $_GET ['status'] != "" && $_GET ['status'] != "undefined" && $_GET ['status'] != "null") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterStatus = $_GET ['status'];
			// * -1: rejected
			// * 0: waiting
			// * 1: completed
			// * 2: assigned
			// * 3: running
			// * 6: blocked
			// * 10: archived
			if ($filterStatus == "rejected") {
				$where .= " p.status = " . Project::$AD_STATUS_REJECTED;
			} else if ($filterStatus == "waiting") {
				$where .= " p.status = " . Project::$AD_STATUS_WAITING;
			} else if ($filterStatus == "completed") {
				$where .= " p.status = " . Project::$AD_STATUS_COMPLETED;
			} else if ($filterStatus == "accepted") {
				$where .= " p.status = " . Project::$AD_STATUS_ACCEPTED;
			} else if ($filterStatus == "assigned") {
				$where .= " p.status = " . Project::$AD_STATUS_ASSIGNED;
			} else if ($filterStatus == "running") {
				$where .= " p.status = " . Project::$AD_STATUS_RUNNING;
			} else if ($filterStatus == "blocked") {
				$where .= " p.status = " . Project::$AD_STATUS_BLOCKED;
			} else if ($filterStatus == "archived") {
				$where .= " p.status = " . Project::$AD_STATUS_ARCHIVED;
			}
		}
		
		// keywords filter (title)
		$filterKeywords = null;
		if (isset ( $_GET ['keywords'] ) && $_GET ['keywords'] != "" && $_GET ['keywords'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterKeywords = $_GET ['keywords'];
			$where .= " (p.title LIKE :keywords ) ";
		}
		
		// MTH PF
		$filterMthPF = null;
		if (isset ( $_GET ['mth_pf'] ) && $_GET ['mth_pf'] != "" && $_GET ['mth_pf'] != "undefined" && $_GET ['mth_pf'] != "null") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$join .= " JOIN p.mthPlatforms pf  ";
			$filterMthPF = intval ( preg_split ( '/_/', $_GET ['mth_pf'] ) [2] );
			$where .= " (pf.id = :mthPF ) ";
		}
		
		// DATE FROM
		$filterDateFrom = null;
		if (isset ( $_GET ['from'] ) && $_GET ['from'] != "" && $_GET ['from'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterDateFrom = $_GET ['from'];
			$where .= " (p.created > :dateFrom ) ";
		}
		
		// DATE TO
		$filterDateTo = null;
		if (isset ( $_GET ['to'] ) && $_GET ['to'] != "" && $_GET ['to'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterDateTo = $_GET ['to'];
			$where .= " (p.created < :dateTo ) ";
		}
		
		$order = " ";
		if (isset ( $_GET ['order'] ) && $_GET ['order'] != "") {
			$filter = true;
			switch (strtolower ( $_GET ['order'] )) {
				case "desc" :
					$order = " ORDER BY p.id DESC";
					break;
				case "asc" :
				default :
					$order = " ORDER BY p.id ASC";
					break;
			}
		}
		
		if ($filter) {
			$query = $entityManager->createQuery ( 'SELECT p FROM Project p ' . $join . $where . $order );
			
			if ($filterStart)
				$query->setFirstResult ( $offset );
			if ($filterLimit)
				$query->setMaxResults ( $maxResults );
				// if ($filterDeleted)
				// $query->setParameter ( 'deleted', $deleted );
				// if ($filterDeleted && ! is_null ( $userID )) {
				// $query->setParameters ( array (
				// 'deleted' => $deleted,
				// 'userID' => $userID
				// ) );
				// }
			$queryParam = Array ();
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			if (! is_null ( $userID ))
				$queryParam ['userID'] = $userID;
			if (! is_null ( $filterKeywords )) {
				$queryParam ['keywords'] = "%" . $filterKeywords . "%";
			}
			if (! is_null ( $filterDateFrom )) {
				$filterDateFrom = date_format ( date_create ( $filterDateFrom ), "Y-m-d" );
				$queryParam ['dateFrom'] = "" . $filterDateFrom . "";
			}
			if (! is_null ( $filterDateTo )) {
				$filterDateTo = date_format ( date_create ( $filterDateTo ), "Y-m-d" );
				$queryParam ['dateTo'] = "" . $filterDateTo . "";
			}
			if (! is_null ( $filterMthPF )) {
				$queryParam ['mthPF'] = "" . $filterMthPF . "";
			}
			$query->setParameters ( $queryParam );
			$projects = $query->getResult ();
			// var_dump ( sizeof ( $projects ) );
			// exit ();
			return $projects;
		}
		
		// no filters
		$projects = $entityManager->getRepository ( 'Project' )->findAll ();
		return $projects;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// COUNT
	
	/**
	 *
	 * @param unknown $user        	
	 * @param unknown $filterStatus        	
	 * @return unknown
	 */
	public static function countProjects($user = null, $filterStatus = null, $userFilter = null) {
		
		// convert GET to param
		if (is_null ( $filterStatus ) && isset ( $_GET ['status'] ) && $_GET ['status'] != "" && $_GET ['status'] != "undefined" && $_GET ['status'] != "null") {
			$filterStatus = $_GET ['status'];
		}
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// check filters
		$filter = false;
		
		$where = "";
		$filterDeleted = false;
		$deleted = null;
		$join = "";
		
		$userID = null;
		if (! is_null ( $user )) {
			$filter = true;
			// construct where
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			if ($userFilter != null && $userFilter == "owner")
				$join .= " JOIN p.owner u  ";
			else if ($userFilter != null && $userFilter == "inCharge")
				$join .= " JOIN p.analystInCharge u  ";
			else if ($userFilter != null && $userFilter == "involved")
				$join .= " JOIN p.analystsInvolved u  ";
			else
				$join .= " JOIN p.owner u  ";
			$where .= " u.id = :userID ";
			$userID = $user;
		}
		
		if (isset ( $_GET ['deleted'] ) && $_GET ['deleted'] != "") { // && is_int ( $_GET ['limit'] )
			$filter = true;
			$filterDeleted = true;
			
			// get deleted value
			if (is_bool ( $_GET ['deleted'] ))
				$deleted = boolval ( $_GET ['deleted'] );
			else {
				if (strtolower ( $_GET ['deleted'] ) == "true") {
					$deleted = true;
				} else {
					$deleted = false;
				}
			}
			
			// construct where
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " p.deleted = :deleted ";
		}
		
		if (! is_null ( $filterStatus )) {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			// * -1: rejected
			// * 0: waiting
			// * 1: completed
			// * 2: assigned
			// * 3: running
			// * 6: blocked
			// * 10: archived
			if ($filterStatus == "rejected") {
				$where .= " p.status = " . Project::$AD_STATUS_REJECTED;
			} else if ($filterStatus == "waiting") {
				$where .= " p.status = " . Project::$AD_STATUS_WAITING;
			} else if ($filterStatus == "completed") {
				$where .= " p.status = " . Project::$AD_STATUS_COMPLETED;
			} else if ($filterStatus == "accepted") {
				$where .= " p.status = " . Project::$AD_STATUS_ACCEPTED;
			} else if ($filterStatus == "assigned") {
				$where .= " p.status = " . Project::$AD_STATUS_ASSIGNED;
			} else if ($filterStatus == "running") {
				$where .= " p.status = " . Project::$AD_STATUS_RUNNING;
			} else if ($filterStatus == "blocked") {
				$where .= " p.status = " . Project::$AD_STATUS_BLOCKED;
			} else if ($filterStatus == "archived") {
				$where .= " p.status = " . Project::$AD_STATUS_ARCHIVED;
			}
		}
		
		// keywords filter (title)
		$filterKeywords = null;
		if (isset ( $_GET ['keywords'] ) && $_GET ['keywords'] != "" && $_GET ['keywords'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterKeywords = $_GET ['keywords'];
			$where .= " (p.title LIKE :keywords ) ";
		}
		
		// MTH PF
		$filterMthPF = null;
		if (isset ( $_GET ['mth_pf'] ) && $_GET ['mth_pf'] != "" && $_GET ['mth_pf'] != "undefined" && $_GET ['mth_pf'] != "null") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$join .= " JOIN p.mthPlatforms pf  ";
			$filterMthPF = intval ( preg_split ( '/_/', $_GET ['mth_pf'] ) [2] );
			$where .= " (pf.id = :mthPF ) ";
		}
		
		// DATE FROM
		$filterDateFrom = null;
		if (isset ( $_GET ['from'] ) && $_GET ['from'] != "" && $_GET ['from'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterDateFrom = $_GET ['from'];
			$where .= " (p.created > :dateFrom ) ";
		}
		
		// DATE TO
		$filterDateTo = null;
		if (isset ( $_GET ['to'] ) && $_GET ['to'] != "" && $_GET ['to'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterDateTo = $_GET ['to'];
			$where .= " (p.created < :dateTo ) ";
		}
		
		$query = $entityManager->createQuery ( 'SELECT COUNT (p.id) FROM Project p ' . $join . $where );
		// if (! is_null ( $userID ))
		// $query->setParameter ( 'userID', $userID );
		// if ($filterDeleted)
		// $query->setParameter ( 'deleted', $deleted );
		// if ($filterDeleted && ! is_null ( $userID )) {
		// $query->setParameters ( array (
		// 'deleted' => $deleted,
		// 'userID' => $userID
		// ) );
		// }
		$queryParam = Array ();
		if ($filterDeleted)
			$queryParam ['deleted'] = $deleted;
		if (! is_null ( $userID ))
			$queryParam ['userID'] = $userID;
		if (! is_null ( $filterKeywords )) {
			$queryParam ['keywords'] = "%" . $filterKeywords . "%";
		}
		if (! is_null ( $filterDateFrom )) {
			$filterDateFrom = date_format ( date_create ( $filterDateFrom ), "Y-m-d" );
			$queryParam ['dateFrom'] = "" . $filterDateFrom . "";
		}
		if (! is_null ( $filterDateTo )) {
			$filterDateTo = date_format ( date_create ( $filterDateTo ), "Y-m-d" );
			$queryParam ['dateTo'] = "" . $filterDateTo . "";
		}
		if (! is_null ( $filterMthPF )) {
			$queryParam ['mthPF'] = "" . $filterMthPF . "";
		}
		$query->setParameters ( $queryParam );
		
		$projectsCount = $query->getResult ();
		// var_dump( $projectsCount[0][1]); exit;
		return $projectsCount [0] [1];
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ONE
	
	/**
	 *
	 * @param long $id        	
	 * @return Project
	 */
	public static function get($id) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$project = $entityManager->getRepository ( 'Project' )->find ( $id );
		return $project;
	}
	
	/**
	 * search a $keyWord in projects title
	 *
	 * @param String $keyWord        	
	 * @return Project(s)
	 */
	public static function search($keyWord) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$query = $entityManager->createQuery ( 'SELECT p FROM Project p WHERE p.title LIKE :search' );
		$query->setParameter ( 'search', "%" . $keyWord . "%" );
		$projects = $query->getResult ();
		
		return $projects;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CREATE
	public static function create($title, $owner,
			/* -- */
			$interestInMthCollaboration, 
			/* -- */
			$demandTypeEqProvisioning, $demandTypeCatalogAllowance, $demandTypeFeasibilityStudy, $demandTypeTraining, $demandTypeDataProcessing, $demandTypeOther,
			/* -- */
			$samplesNumber,
			/* -- */
			$thematicWords, $subThematicWords, $targeted, $mthPlatforms, $canBeForwardedToCoPartner, 
			/* -- */
			$scientificContext, $scientificContextFile, 
			/* -- */
			$financialContextIsProjectFinanced, $financialContextIsProjectInProvisioning, $financialContextIsProjectOnOwnSupply, $financialContextIsProjectNotFinanced, 
			/* -- */
			$financialContextIsProjectEU, $financialContextIsProjectANR, $financialContextIsProjectNational, $financialContextIsProjectRegional, $financialContextIsProjectCompagnyTutorship, $financialContextIsProjectOwnResourcesLaboratory, $financialContextIsProjectInternationalOutsideEU, $financialContextIsProjectOther,
			/* -- */
			$financialContextIsProjectOtherValue) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// create and save in the database
		$project = new Project ( $title, $owner );
		
		// ALL DATUM
		$project->setInterestInMthCollaboration ( $interestInMthCollaboration );
		
		$project->setDemandTypeEqProvisioning ( $demandTypeEqProvisioning );
		$project->setDemandTypeCatalogAllowance ( $demandTypeCatalogAllowance );
		$project->setDemandTypeFeasibilityStudy ( $demandTypeFeasibilityStudy );
		$project->setDemandTypeTraining ( $demandTypeTraining );
		$project->setDemandTypeDataProcessing ( $demandTypeDataProcessing );
		$project->setDemandTypeOther ( $demandTypeOther );
		
		$project->setSamplesNumber ( $samplesNumber );
		
		if (! is_null ( $thematicWords )) {
			// recoved id -> obj
			$keyWords = KeywordManagementService::getKeywordsByIDs ( $thematicWords );
			$project->setThematicWords ( $keyWords );
		}
		if (! is_null ( $subThematicWords )) {
			// recoved id -> obj
			$keyWords = KeywordManagementService::getSubKeywordsByIDs ( $subThematicWords );
			$project->setSubThematicWords ( $keyWords );
		}
		
		$project->setTargeted ( $targeted );
		
		if (! is_null ( $mthPlatforms )) {
			// recoved id -> obj? (via mthMgmtService)
			$platforms = MTHPlatformManagementService::getMTHPlatformsByIDs ( $mthPlatforms );
			$project->setMthPlatforms ( $platforms );
		}
		
		$project->setCanBeForwardedToCoPartner ( $canBeForwardedToCoPartner );
		
		$project->setScientificContext ( $scientificContext );
		$project->setScientificContextFile ( $scientificContextFile );
		
		$project->setFinancialContextIsProjectFinanced ( $financialContextIsProjectFinanced );
		$project->setFinancialContextIsProjectInProvisioning ( $financialContextIsProjectInProvisioning );
		$project->setFinancialContextIsProjectOnOwnSupply ( $financialContextIsProjectOnOwnSupply );
		$project->setFinancialContextIsProjectNotFinanced ( $financialContextIsProjectNotFinanced );
		
		$project->setFinancialContextIsProjectEU ( $financialContextIsProjectEU );
		$project->setFinancialContextIsProjectANR ( $financialContextIsProjectANR );
		$project->setFinancialContextIsProjectNational ( $financialContextIsProjectNational );
		$project->setFinancialContextIsProjectRegional ( $financialContextIsProjectRegional );
		$project->setFinancialContextIsProjectCompagnyTutorship ( $financialContextIsProjectCompagnyTutorship );
		$project->setFinancialContextIsProjectOwnResourcesLaboratory ( $financialContextIsProjectOwnResourcesLaboratory );
		$project->setFinancialContextIsProjectInternationalOutsideEU ( $financialContextIsProjectInternationalOutsideEU );
		$project->setFinancialContextIsProjectOther ( $financialContextIsProjectOther );
		$project->setFinancialContextIsProjectOtherValue ( $financialContextIsProjectOtherValue );
		
		$entityManager->persist ( $project );
		$entityManager->flush ();
		
		// create new Event
		EventManagementService::createProjectEvent ( $owner, ProjectEvent::$EVENT_TYPE_NEW_PROJECT, $project );
		
		return $project->getId ();
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// UPDATE
	public static function update($id, $title,
			/* -- */
// 			$analystInCharge, $analystsInvolved,
			/* -- */
			$interestInMthCollaboration, 
			/* -- */
			$demandTypeEqProvisioning, $demandTypeCatalogAllowance, $demandTypeFeasibilityStudy, $demandTypeTraining, $demandTypeDataProcessing, $demandTypeOther,
			/* -- */
			$samplesNumber,
			/* -- */
			$thematicWords, $subThematicWords, $targeted, $mthPlatforms, $canBeForwardedToCoPartner, 
			/* -- */
			$scientificContext, $scientificContextFile, 
			/* -- */
			$financialContextIsProjectFinanced, $financialContextIsProjectInProvisioning, $financialContextIsProjectOnOwnSupply, $financialContextIsProjectNotFinanced, 
			/* -- */
			$financialContextIsProjectEU, $financialContextIsProjectANR, $financialContextIsProjectNational, $financialContextIsProjectRegional, $financialContextIsProjectCompagnyTutorship, $financialContextIsProjectOwnResourcesLaboratory, $financialContextIsProjectInternationalOutsideEU, $financialContextIsProjectOther,
			/* -- */
			$financialContextIsProjectOtherValue, 
			/* -- */
			$userSource = null) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get project from the database
		$project = null;
		try {
			$project = ProjectManagementService::get ( $id );
		} catch ( Exception $em ) {
			// no entity
			return false;
		}
		if (is_null ( $project ) || $project->isDeleted ())
			return false;
		
		$project->setTitle ( $title );
		
		// $analystInCharge, $analystsInvolved,
		// $project->setAnalystInCharge($analystInCharge);
		// if (! is_null ( $analystsInvolved )) {
		// // recoved id -> obj? (via userMgmtService)
		// $project->setAnalystInCharge ( $analystsInvolved );
		// }
		// $interestInMthCollaboration, $demandTypeEqProvisioning, $demandTypeCatalogAllowance, $demandTypeFeasibilityStudy, $demandTypeTraining, $samplesNumber,
		$project->setInterestInMthCollaboration ( $interestInMthCollaboration );
		$project->setDemandTypeEqProvisioning ( $demandTypeEqProvisioning );
		$project->setDemandTypeCatalogAllowance ( $demandTypeCatalogAllowance );
		$project->setDemandTypeFeasibilityStudy ( $demandTypeFeasibilityStudy );
		$project->setDemandTypeTraining ( $demandTypeTraining );
		$project->setDemandTypeDataProcessing ( $demandTypeDataProcessing );
		$project->setDemandTypeOther ( $demandTypeOther );
		// $thematicWords, $targeted, $mthPlatforms, $canBeForwardedToCoPartner,
		$project->setSamplesNumber ( $samplesNumber );
		if (! is_null ( $thematicWords )) {
			// recoved id -> obj? (via cloudWordsMgmtService)
			$project->setThematicWords ( $thematicWords );
		}
		if (! is_null ( $subThematicWords )) {
			// recoved id -> obj? (via cloudWordsMgmtService)
			$project->setSubThematicWords ( $subThematicWords );
		}
		$project->setTargeted ( $targeted );
		if (! is_null ( $mthPlatforms )) {
			// recoved id -> obj? (via mthMgmtService)
			$project->setMthPlatforms ( $mthPlatforms );
		}
		$project->setCanBeForwardedToCoPartner ( $canBeForwardedToCoPartner );
		// $scientificContext, $scientificContextFile,
		$project->setScientificContext ( $scientificContext );
		$project->setScientificContextFile ( $scientificContextFile );
		// $financialContextIsProjectFinanced, $financialContextIsProjectInProvisioning, $financialContextIsProjectOnOwnSupply, $financialContextIsProjectNotFinanced,
		$project->setFinancialContextIsProjectFinanced ( $financialContextIsProjectFinanced );
		$project->setFinancialContextIsProjectInProvisioning ( $financialContextIsProjectInProvisioning );
		$project->setFinancialContextIsProjectOnOwnSupply ( $financialContextIsProjectOnOwnSupply );
		$project->setFinancialContextIsProjectNotFinanced ( $financialContextIsProjectNotFinanced );
		// $financialContextIsProjectEU, $financialContextIsProjectANR, $financialContextIsProjectNational, $financialContextIsProjectRegional, $financialContextIsProjectCompagnyTutorship, $financialContextIsProjectOther,
		$project->setFinancialContextIsProjectEU ( $financialContextIsProjectEU );
		$project->setFinancialContextIsProjectANR ( $financialContextIsProjectANR );
		$project->setFinancialContextIsProjectNational ( $financialContextIsProjectNational );
		$project->setFinancialContextIsProjectRegional ( $financialContextIsProjectRegional );
		$project->setFinancialContextIsProjectCompagnyTutorship ( $financialContextIsProjectCompagnyTutorship );
		$project->setFinancialContextIsProjectOwnResourcesLaboratory ( $financialContextIsProjectOwnResourcesLaboratory );
		$project->setFinancialContextIsProjectInternationalOutsideEU ( $financialContextIsProjectInternationalOutsideEU );
		$project->setFinancialContextIsProjectOther ( $financialContextIsProjectOther );
		// $financialContextIsProjectOtherValue
		$project->setFinancialContextIsProjectOtherValue ( $financialContextIsProjectOtherValue );
		
		$project->setUpdated ();
		
		$entityManager->persist ( $project );
		$entityManager->flush ();
		
		// create new Event
		if ($userSource != null)
			EventManagementService::createProjectEvent ( $userSource, UserEvent::$EVENT_TYPE_UPDATE_PROJECT__informations, $project );
		
		return true;
	}
	
	/**
	 *
	 * @param unknown $project        	
	 */
	public static function updateObject($project, $isAdmin = false, $userSource = null) {
		if (is_null ( $project ))
			return false;
		
		$projectInDB = null;
		$updated = false;
		$updateBasicData = false;
		$entityManager = $GLOBALS ['entityManager'];
		
		if (is_a ( $project, "Project" )) {
			$projectInDB = ProjectManagementService::get ( $project->getId () );
			
			// $projectInDB->setTitle ( $project->getTitle () );
			if ($projectInDB->getStatus () != $project->getStatus ()) {
				$projectInDB->setStatus ( $project->getStatus () );
				$updated = true;
				if ($userSource != null) {
					if ($project->getStatus () == "assigned") {
						EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__assigned, $projectInDB );
					} else if ($project->getStatus () == "accepted") {
						EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__accepted, $projectInDB );
					} else if ($project->getStatus () == "completed") {
						EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__completed, $projectInDB );
					} else if ($project->getStatus () == "running") {
						EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__running, $projectInDB );
					} else if ($project->getStatus () == "rejected") {
						EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__rejected, $projectInDB );
					} else if ($project->getStatus () == "blocked") {
						EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__blocked, $projectInDB );
					} else if ($project->getStatus () == "archived") {
						EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__archived, $projectInDB );
					}
				}
			}
			if ($projectInDB->getAnalystInCharge () != $project->getAnalystInCharge ()) {
				$projectInDB->setAnalystInCharge ( $project->getAnalystInCharge () );
				$updated = true;
				if ($userSource != null) {
					EventManagementService::createUserEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_USER__set_in_charge, $project->getAnalystInCharge () );
					EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__new_analyst_in_charge, $projectInDB );
				}
			}
			if ($projectInDB->getAnalystsInvolved () != $project->getAnalystsInvolved ()) {
				// $projectInDB->setAnalystsInvolved ( new \Doctrine\Common\Collections\ArrayCollection () );
				$hql_drop = "DELETE FROM `users_involved_in_projects` WHERE `project_id` = " . intval ( $projectInDB->getId () ) . " ";
				$entityManager->getConnection ()->executeUpdate ( $hql_drop );
				$updated = true;
				if ($userSource != null) {
					EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__new_analyst_involved, $projectInDB );
					foreach ( $project->getAnalystsInvolved () as $k => $user ) {
						// $projectInDB->getAnalystsInvolved ()->add ( $user );
						$user->getProjectsInvolded ()->add ( $projectInDB );
						EventManagementService::createUserEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_USER__set_involved, $user );
					}
				}
			}
			
			// other ($updateBasicData = true)
			// checkbox
			if ($projectInDB->getDemandTypeEqProvisioning () != $project->getDemandTypeEqProvisioning ()) {
				$projectInDB->setDemandTypeEqProvisioning ( $project->getDemandTypeEqProvisioning () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getDemandTypeCatalogAllowance () != $project->getDemandTypeCatalogAllowance ()) {
				$projectInDB->setDemandTypeCatalogAllowance ( $project->getDemandTypeCatalogAllowance () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getDemandTypeFeasibilityStudy () != $project->getDemandTypeFeasibilityStudy ()) {
				$projectInDB->setDemandTypeFeasibilityStudy ( $project->getDemandTypeFeasibilityStudy () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getDemandTypeTraining () != $project->getDemandTypeTraining ()) {
				$projectInDB->setDemandTypeTraining ( $project->getDemandTypeTraining () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getDemandTypeDataProcessing () != $project->getDemandTypeDataProcessing ()) {
				$projectInDB->setDemandTypeDataProcessing ( $project->getDemandTypeDataProcessing () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getDemandTypeOther () != $project->getDemandTypeOther ()) {
				$projectInDB->setDemandTypeOther ( $project->getDemandTypeOther () );
				$updateBasicData = true;
				$updated = true;
			}
			// radio
			if ($projectInDB->getCanBeForwardedToCoPartner () != $project->getCanBeForwardedToCoPartner ()) {
				$projectInDB->setCanBeForwardedToCoPartner ( $project->getCanBeForwardedToCoPartner () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getTargeted () != $project->getTargeted ()) {
				$projectInDB->setTargeted ( $project->getTargeted () );
				$updateBasicData = true;
				$updated = true;
			}
			// textarea
			if ($projectInDB->getInterestInMthCollaboration () != $project->getInterestInMthCollaboration ()) {
				$projectInDB->setInterestInMthCollaboration ( $project->getInterestInMthCollaboration () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getScientificContext () != $project->getScientificContext ()) {
				$projectInDB->setScientificContext ( $project->getScientificContext () );
				$updateBasicData = true;
				$updated = true;
			}
			// select single
			if ($projectInDB->getSamplesNumber () != $project->getSamplesNumber ()) {
				$projectInDB->setSamplesNumber ( $project->getSamplesNumber () );
				$updateBasicData = true;
				$updated = true;
			}
			// select multi - mth pf
			if ($projectInDB->getMthPlatforms () != $project->getMthPlatforms ()) {
				$projectInDB->setMthPlatforms ( $project->getMthPlatforms () );
				$updateBasicData = true;
				$updated = true;
			}
			// select multi - financial
			if ($projectInDB->getFinancialContextIsProjectFinanced () != $project->getFinancialContextIsProjectFinanced ()) {
				$projectInDB->setFinancialContextIsProjectFinanced ( $project->getFinancialContextIsProjectFinanced () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectInProvisioning () != $project->getFinancialContextIsProjectInProvisioning ()) {
				$projectInDB->setFinancialContextIsProjectInProvisioning ( $project->getFinancialContextIsProjectInProvisioning () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectOnOwnSupply () != $project->getFinancialContextIsProjectOnOwnSupply ()) {
				$projectInDB->setFinancialContextIsProjectOnOwnSupply ( $project->getFinancialContextIsProjectOnOwnSupply () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectNotFinanced () != $project->getFinancialContextIsProjectNotFinanced ()) {
				$projectInDB->setFinancialContextIsProjectNotFinanced ( $project->getFinancialContextIsProjectNotFinanced () );
				$updateBasicData = true;
				$updated = true;
			}
			// select multi - financial bis
			if ($projectInDB->getScientificContextFile () != $project->getScientificContextFile ()) {
				$projectInDB->setFinancialContextIsProjectEU ( $project->getScientificContextFile () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectANR () != $project->getFinancialContextIsProjectANR ()) {
				$projectInDB->setFinancialContextIsProjectANR ( $project->getFinancialContextIsProjectANR () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectNational () != $project->getFinancialContextIsProjectNational ()) {
				$projectInDB->setFinancialContextIsProjectNational ( $project->getFinancialContextIsProjectNational () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectRegional () != $project->getFinancialContextIsProjectRegional ()) {
				$projectInDB->setFinancialContextIsProjectRegional ( $project->getFinancialContextIsProjectRegional () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectCompagnyTutorship () != $project->getFinancialContextIsProjectCompagnyTutorship ()) {
				$projectInDB->setFinancialContextIsProjectCompagnyTutorship ( $project->getFinancialContextIsProjectCompagnyTutorship () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectOwnResourcesLaboratory () != $project->getFinancialContextIsProjectOwnResourcesLaboratory ()) {
				$projectInDB->setFinancialContextIsProjectOwnResourcesLaboratory ( $project->getFinancialContextIsProjectOwnResourcesLaboratory () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectInternationalOutsideEU () != $project->getFinancialContextIsProjectInternationalOutsideEU ()) {
				$projectInDB->setFinancialContextIsProjectInternationalOutsideEU ( $project->getFinancialContextIsProjectInternationalOutsideEU () );
				$updateBasicData = true;
				$updated = true;
			}
			if ($projectInDB->getFinancialContextIsProjectOther () != $project->getFinancialContextIsProjectOther ()) {
				$projectInDB->setFinancialContextIsProjectOther ( $project->getFinancialContextIsProjectOther () );
				$updateBasicData = true;
				$updated = true;
			}
			
			// file
			if ($projectInDB->getScientificContextFile () != $project->getScientificContextFile ()) {
				$projectInDB->setScientificContextFile ( $project->getScientificContextFile () );
				$updateBasicData = true;
				$updated = true;
			}
			// text
			if ($projectInDB->getFinancialContextIsProjectOtherValue () != $project->getFinancialContextIsProjectOtherValue ()) {
				$projectInDB->setFinancialContextIsProjectOtherValue ( $project->getFinancialContextIsProjectOtherValue () );
				$updateBasicData = true;
				$updated = true;
			}
			// cloudwords
			if ($projectInDB->getThematicWords () != $project->getThematicWords ()) {
				$projectInDB->setThematicWords ( $project->getThematicWords () );
				$updateBasicData = true;
				$updated = true;
			}
			// subcloudwords
			if ($projectInDB->getSubThematicWords () != $project->getSubThematicWords ()) {
				$projectInDB->setSubThematicWords ( $project->getSubThematicWords () );
				$updateBasicData = true;
				$updated = true;
			}
		} else { // case of json object
			$projectInDB = ProjectManagementService::get ( $project ['id'] );
			
			// TODO update via JSON
		}
		
		// in db!!!
		if ($updated) {
			$projectInDB->setUpdated ();
			$entityManager->persist ( $projectInDB );
			$entityManager->flush ();
		}
		
		// create new Event
		if ($userSource != null && $updateBasicData) {
			EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__informations, $projectInDB );
		}
		
		return true;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// DELETE
	/**
	 *
	 * @param unknown $id        	
	 * @return boolean
	 */
	public static function delete($id, $userSource = null) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get project from the database
		$project = ProjectManagementService::get ( $id );
		
		if (is_null ( $project ) || $project->isDeleted ())
			return false;
		
		$project->delete ();
		
		$project->setUpdated ();
		
		$entityManager->persist ( $project );
		$entityManager->flush ();
		
		// create new Event
		if ($userSource != null)
			EventManagementService::createProjectEvent ( $userSource, UserEvent::$EVENT_TYPE_UPDATE_PROJECT__deleted, $project );
		
		return true;
	}
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// OTHER
	
	/**
	 *
	 * @param unknown $id        	
	 * @param unknown $userSource        	
	 * @return boolean
	 */
	public static function archive($id, $userSource = null) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get project from the database
		$project = ProjectManagementService::get ( $id );
		if (is_null ( $project ) || $project->isDeleted () || $project->getStatus () == "archived")
			return false;
		$project->setStatus ( Project::$AD_STATUS_ARCHIVED );
		$project->setUpdated ();
		$entityManager->persist ( $project );
		$entityManager->flush ();
		// create new Event
		if ($userSource != null)
			EventManagementService::createProjectEvent ( $userSource, UserEvent::$EVENT_TYPE_UPDATE_PROJECT__archived, $project );
		return true;
	}
	
	/**
	 *
	 * @param unknown $nbYears        	
	 * @param unknown $userSource        	
	 */
	public static function archiveOlderThan($nbYears = 99, $userSource = null) {
		$_GET ["to"] = date ( "Y-m-d", strtotime ( "now -" . intval ( $nbYears ) . " years" ) );
		$listProjects = ProjectManagementService::getProjects ( null, null );
		foreach ( $listProjects as $k => $project ) {
			ProjectManagementService::archive ( $project->getId (), $userSource );
		}
		return true;
	}
	
	// ...
}