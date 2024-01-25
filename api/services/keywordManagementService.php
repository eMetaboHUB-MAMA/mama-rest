<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA PORJECTS FUNCTIONS
require_once "../vendor/autoload.php";
// Data Model
require_once "../data-model/ThematicCloudWord.class.php";
require_once "../data-model/SubThematicCloudWord.class.php";
// ext DM
require_once "../data-model/Project.class.php";

// //////////////////////////////////////////////////////////////////////////////////////////////
/**
 *
 * @param Event $a        	
 * @param Event $b        	
 * @return number
 */
function sortByKeywordsIds($a, $b) {
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
class KeywordManagementService {
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ALL
	/**
	 * Get all events (or filter with $_GET fields)
	 *
	 * @return List of Keyword(s)
	 */
	public static function getKeywords() {
		
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
			$query = $entityManager->createQuery ( 'SELECT k FROM ThematicCloudWord k ' . $join . $where . $order );
			$queryParam = Array ();
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			$query->setParameters ( $queryParam );
			if ($filterStart)
				$query->setFirstResult ( $offset );
			if ($filterLimit)
				$query->setMaxResults ( $maxResults );
			$keywords = $query->getResult ();
			return $keywords;
		}
		
		// no filters
		$keywords = $entityManager->getRepository ( 'ThematicCloudWord' )->findAll ();
		return $keywords;
	}
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ONE
	public static function get($id) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$project = $entityManager->getRepository ( 'ThematicCloudWord' )->find ( $id );
		return $project;
	}
	public static function getKeywordsByIDs($tabOfIds) {
		
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
		$query = $entityManager->createQuery ( 'SELECT k FROM ThematicCloudWord k WHERE (' . $where . ')' );
		$query->setParameters ( $queryParam );
		
		$keywords = $query->getResult ();
		return $keywords;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CREATE
	public static function create($word, $user) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// // create and save in the database
		$thematicCloudWord = new ThematicCloudWord ( $word );
		
		$thematicCloudWord->setUpdated ();
		
		$entityManager->persist ( $thematicCloudWord );
		$entityManager->flush ();
		
		// create event admin lvl
		EventManagementService::createAdminEvent ( $user, Event::$EVENT_TYPE_ADMIN_NEW_KEYWORD, $word, null );
		
		return $thematicCloudWord->getId ();
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// UPDATE
	public static function update($id, $keyword, $deleted, $user) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get
		$thematicCloudWord = KeywordManagementService::get ( $id );
		$oldName = $thematicCloudWord->getWord ();
		$newName = null;
		$action = Event::$EVENT_TYPE_ADMIN;
		
		if (is_null ( $thematicCloudWord ))
			return false;
			
			// update
		if (! is_null ( $keyword )) {
			$thematicCloudWord->setWord ( $keyword );
			$newName = $keyword;
			$action = Event::$EVENT_TYPE_ADMIN_UPDATE_KEYWORD;
		}
		if (! is_null ( $deleted )) {
			$thematicCloudWord->setDeleted ( $deleted );
			if ($deleted)
				$action = Event::$EVENT_TYPE_ADMIN_DELETE_KEYWORD;
			else
				$action = Event::$EVENT_TYPE_ADMIN_RESTORE_KEYWORD;
		}
		$thematicCloudWord->setUpdated ();
		
		// save
		$entityManager->persist ( $thematicCloudWord );
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
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ALL
	/**
	 * Get all events (or filter with $_GET fields)
	 *
	 * @return List of Keyword(s)
	 */
	public static function getSubKeywords() {
		
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
			$query = $entityManager->createQuery ( 'SELECT k FROM SubThematicCloudWord k ' . $join . $where . $order );
			$queryParam = Array ();
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			$query->setParameters ( $queryParam );
			if ($filterStart)
				$query->setFirstResult ( $offset );
			if ($filterLimit)
				$query->setMaxResults ( $maxResults );
			$keywords = $query->getResult ();
			return $keywords;
		}
		
		// no filters
		$keywords = $entityManager->getRepository ( 'SubThematicCloudWord' )->findAll ();
		return $keywords;
	}
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ONE
	public static function getSub($id) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$project = $entityManager->getRepository ( 'SubThematicCloudWord' )->find ( $id );
		return $project;
	}
	public static function getSubKeywordsByIDs($tabOfIds) {
		
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
		$query = $entityManager->createQuery ( 'SELECT k FROM SubThematicCloudWord k WHERE (' . $where . ')' );
		$query->setParameters ( $queryParam );
		
		$keywords = $query->getResult ();
		return $keywords;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CREATE
	public static function createSub($word, $user) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// // create and save in the database
		$thematicCloudWord = new SubThematicCloudWord ( $word );
		
		$thematicCloudWord->setUpdated ();
		
		$entityManager->persist ( $thematicCloudWord );
		$entityManager->flush ();
		
		// create event admin lvl
		EventManagementService::createAdminEvent ( $user, Event::$EVENT_TYPE_ADMIN_NEW_SUBKEYWORD, $word, null );
		
		return $thematicCloudWord->getId ();
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// UPDATE
	public static function updateSub($id, $keyword, $deleted, $user) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// get
		$thematicCloudWord = KeywordManagementService::getSub ( $id );
		$oldName = $thematicCloudWord->getWord ();
		$newName = null;
		$action = Event::$EVENT_TYPE_ADMIN;
		
		if (is_null ( $thematicCloudWord ))
			return false;
			
			// update
		if (! is_null ( $keyword )) {
			$thematicCloudWord->setWord ( $keyword );
			$newName = $keyword;
			$action = Event::$EVENT_TYPE_ADMIN_UPDATE_SUBKEYWORD;
		}
		if (! is_null ( $deleted )) {
			$thematicCloudWord->setDeleted ( $deleted );
			if ($deleted)
				$action = Event::$EVENT_TYPE_ADMIN_DELETE_SUBKEYWORD;
			else
				$action = Event::$EVENT_TYPE_ADMIN_RESTORE_SUBKEYWORD;
		}
		$thematicCloudWord->setUpdated ();
		
		// save
		$entityManager->persist ( $thematicCloudWord );
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