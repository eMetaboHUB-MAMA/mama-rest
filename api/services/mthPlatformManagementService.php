<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA PORJECTS FUNCTIONS
require_once "../vendor/autoload.php";
// Data Model
require_once "../data-model/MTHplatform.class.php";
// ext DM
require_once "../data-model/Project.class.php";

// //////////////////////////////////////////////////////////////////////////////////////////////
/**
 *
 * @param Event $a        	
 * @param Event $b        	
 * @return number
 */
function sortByMTHPlatformsIds($a, $b) {
	if ($a->getId () == $b->getId ()) {
		return 0;
	}
	return ($a->getId () < $b->getId ()) ? - 1 : 1;
}

/**
 *
 * @author Nils Paulhe
 *        
 */
class MTHPlatformManagementService {
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ALL
	/**
	 * Get all events (or filter with $_GET fields)
	 *
	 * @return List of MTHPlatform(s)
	 */
	public static function getMTHPlatforms() {
		
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
		
		if (isset ( $_GET ['start'] ) && $_GET ['start'] != "") {
			$filter = true;
			$filterStart = true;
			$offset = intval ( $_GET ['start'] );
		}
		
		if (isset ( $_GET ['limit'] ) && $_GET ['limit'] != "") {
			$filter = true;
			$filterLimit = true;
			$maxResults = intval ( $_GET ['limit'] );
		}
		
		if (isset ( $_GET ['deleted'] ) && $_GET ['deleted'] != "") {
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
			$where .= " k.deleted = :deleted ";
		}
		
		$order = " ";
		if (isset ( $_GET ['order'] ) && $_GET ['order'] != "") {
			$filter = true;
			switch (strtolower ( $_GET ['order'] )) {
				case "desc" :
					$order = " ORDER BY k.id DESC";
					break;
				case "asc" :
				default :
					$order = " ORDER BY k.id ASC";
					break;
			}
		}
		
		if ($filter) {
			$query = $entityManager->createQuery ( 'SELECT k FROM MTHplatform k ' . $join . $where . $order );
			$queryParam = Array ();
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			$query->setParameters ( $queryParam );
			if ($filterStart)
				$query->setFirstResult ( $offset );
			if ($filterLimit)
				$query->setMaxResults ( $maxResults );
			$mthPlatforms = $query->getResult ();
			return $mthPlatforms;
		}
		
		// no filters
		$mthPlatforms = $entityManager->getRepository ( 'MTHplatform' )->findAll ();
		return $mthPlatforms;
	}
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ONE
	public static function get($id) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$project = $entityManager->getRepository ( 'MTHplatform' )->find ( $id );
		return $project;
	}
	public static function getMTHPlatformsByIDs($tabOfIds) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// process
		$where = "";
		$queryParam = Array ();
		$i = 0;
		foreach ( $tabOfIds as $k => $v ) {
			if ($where != "")
				$where .= " OR ";
			$where .= " k.id = :id_" . $i;
			$queryParam ['id_' . $i] = intval ( $v );
			$i ++;
		}
		
		// run
		$query = $entityManager->createQuery ( 'SELECT k FROM MTHplatform k WHERE (' . $where . ')' );
		$query->setParameters ( $queryParam );
		
		$mthPlatforms = $query->getResult ();
		return $mthPlatforms;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CREATE
	public static function create($platform, $user) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// // create and save in the database
		$mthPlatform = new MTHplatform ( $platform );
		
		$mthPlatform->setUpdated ();
		
		$entityManager->persist ( $mthPlatform );
		$entityManager->flush ();
		
		// create event admin lvl
		EventManagementService::createAdminEvent ( $user, Event::$EVENT_TYPE_ADMIN_NEW_MTH_PF, $platform, null );
		
		return $mthPlatform->getId ();
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// UPDATE
	public static function update($id, $mthPlatformName, $user) { // $deleted
	                                                          
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get
		$mthPlatform = MTHPlatformManagementService::get ( $id );
		$oldName = $mthPlatform->getName ();
		$newName = null;
		$action = Event::$EVENT_TYPE_ADMIN;
		
		if (is_null ( $mthPlatform ))
			return false;
			
			// update
		if (! is_null ( $mthPlatformName )) {
			$mthPlatform->setName ( $mthPlatformName );
			$newName = $mthPlatformName;
			$action = Event::$EVENT_TYPE_ADMIN_UPDATE_MTH_PF;
		}
		// if (! is_null ( $deleted )) {
		// $mthPlatform->setDeleted ( $deleted );
		// if ($deleted)
		// $action = Event::$EVENT_TYPE_ADMIN_DELETE_MTH_PF;
		// else
		// $action = Event::$EVENT_TYPE_ADMIN_RESTORE_MTH_PF;
		// }
		$mthPlatform->setUpdated ();
		
		// save
		$entityManager->persist ( $mthPlatform );
		$entityManager->flush ();
		
		// create event admin lvl
		EventManagementService::createAdminEvent ( $user, $action, $oldName, $newName );
		
		return true;
	}
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// DELETE
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// OTHER
	
	// ...
}