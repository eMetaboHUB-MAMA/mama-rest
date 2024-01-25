<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA PORJECTS FUNCTIONS
require_once __DIR__ . "/../../vendor/autoload.php";
// Data Model
// require_once __DIR__ . "/../../data-model/User.class.php";
require_once __DIR__ . "/../../data-model/Project.class.php";
require_once __DIR__ . "/../../data-model/ProjectExtraData.class.php";
require_once __DIR__ . "/../../data-model/Message.class.php";
// require_once __DIR__ . "/../../data-model/ThematicCloudWord.class.php";
// require_once __DIR__ . "/../../data-model/MTHplatform.class.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class MessageManagementService {
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ALL
	/**
	 * Get all messages (or filter with $_GET fields)
	 *
	 * @return List of Message(s)
	 */
	public static function getMessages($user = null, $userFilter = null, $project = null, $projectFilter = null, $user2project = null, $user2projectFilter = null, $noUser = false) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// check filters
		$filter = false;
		$filterStart = false;
		$filterLimit = false;
		
		// extra filter first
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
		
		$where = "";
		$whereFilter = "";
		$filterDeleted = false;
		$deleted = null;
		$join = "";
		
		// filter on a user
		$userID = null;
		if (! is_null ( $user ) && ! $noUser) {
			$filter = true;
			// construct where
			// if ($where == "") {
			// $where = " WHERE ";
			// } else {
			// $where .= " AND ";
			// }
			if ($userFilter != null && $userFilter == "sender")
				// $join .= " JOIN m.fromUser u ";
				// $join .= " JOIN User u WITH m.fromUser = u.id ";
				$whereFilter .= " m.fromUser = :userID ";
			else if ($userFilter != null && $userFilter == "receiver")
				// $join .= " JOIN m.toUser u ";
				// $join .= " JOIN User u WITH m.toUser = u.id ";
				$whereFilter .= " ( m.toUser = :userID ) ";
			else if ($userFilter != null && $userFilter == "all" && $user2projectFilter != "users")
				$whereFilter .= " (m.toUser = :userID OR m.fromUser = :userID)";
			else
				// $join .= " JOIN m.fromUser u ";
				// $join .= " JOIN User u WITH m.fromUser = u.id ";
				$whereFilter .= " m.toUser = :userID ";
				// $whereFilter .= " u.id = :userID ";
			$userID = $user;
			if ($noUser)
				$userID = - 1;
		}
		
		// filter on a project
		$projectID = null;
		// $userIDo1 = null;
		// $userIDo2 = null;
		// $userIDo3 = null;
		$arrayProject = null;
		if (! is_null ( $project )) {
			$filter = true;
			// construct where
			if ($whereFilter == "") {
				$whereFilter = " ( ";
			} else {
				$whereFilter .= " OR ( ";
			}
			if ($projectFilter != null && $projectFilter == "sender")
				$join .= " JOIN m.fromProject p ";
			else if ($projectFilter != null && $projectFilter == "receiver")
				$join .= " JOIN m.toProject p ";
			else
				$join .= " JOIN m.toProject p ";
			
			$whereFilter .= " ( p.id = :projectID ) ";
			$whereFilter .= " ) ";
			// $userID = $user;
			$projectID = $project;
		} else if ((! is_null ( $user2project )) && $user2projectFilter != "users") {
			// filter projects where user is in:all / in:owner / in:manager / in:involved
			$filter = true;
			// construct where
			if ($whereFilter == "") {
				$whereFilter = " ( ";
			} else {
				$whereFilter .= " OR ( ";
			}
			if ($user2projectFilter != null && $user2projectFilter == "all") {
				$_GET ['start'] = "";
				$_GET ['limit'] = "";
				$arrayProject = array ();
				foreach ( ProjectManagementService::getProjects ( $user2project, "owner" ) as &$project ) {
					// echo $project->getId () . "\n";
					array_push ( $arrayProject, $project->getId () );
				}
				foreach ( ProjectManagementService::getProjects ( $user2project, "inCharge" ) as &$project ) {
					if (! in_array ( $project->getId (), $arrayProject, true )) {
						// echo $project->getId () . "\n";
						array_push ( $arrayProject, $project->getId () );
					}
				}
				foreach ( ProjectManagementService::getProjects ( $user2project, "involved" ) as &$project ) {
					if (! in_array ( $project->getId (), $arrayProject, true )) {
						// echo $project->getId () . "\n";
						array_push ( $arrayProject, $project->getId () );
					}
				}
				$whereFilter .= " ( m.toProject IN (:arrayProject) ) ";
			} else if ($user2projectFilter != null && $user2projectFilter == "owner") {
				$_GET ['start'] = "";
				$_GET ['limit'] = "";
				$arrayProject = array ();
				foreach ( ProjectManagementService::getProjects ( $user2project, "owner" ) as &$project ) {
					// echo $project->getId () . "\n";
					array_push ( $arrayProject, $project->getId () );
				}
				$whereFilter .= " ( m.toProject IN (:arrayProject)  ) ";
			} else {
				$_GET ['start'] = "";
				$_GET ['limit'] = "";
				$arrayProject = array ();
				foreach ( ProjectManagementService::getProjects ( $user2project, $user2projectFilter ) as &$project ) {
					// echo $project->getId () . "\n";
					array_push ( $arrayProject, $project->getId () );
				}
				$whereFilter .= " ( m.toProject IN (:arrayProject)  ) ";
			}
			
			$whereFilter .= " ) ";
		}
		
		// $whereFilter
		if ($whereFilter != "") {
			$where = " WHERE ( " . $whereFilter . " ) ";
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
			$where .= " m.deleted = :deleted ";
		}
		
		// filter user from
		// filter project from
		// filter user to
		// filter project to
		
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
			$where .= " (m.created >= :dateFrom ) ";
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
			$where .= " (m.created <= :dateTo ) ";
		}
		
		// keywords filter (message)
		$filterKeywords = null;
		if (isset ( $_GET ['keywords'] ) && $_GET ['keywords'] != "" && $_GET ['keywords'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterKeywords = $_GET ['keywords'];
			$where .= " (m.message LIKE :keywords ) ";
		}
		
		$order = " ";
		if (isset ( $_GET ['order'] ) && $_GET ['order'] != "") {
			$filter = true;
			switch (strtolower ( $_GET ['order'] )) {
				case "desc" :
					$order = " ORDER BY m.id DESC";
					break;
				case "asc" :
				default :
					$order = " ORDER BY m.id ASC";
					break;
			}
		}
		if ($filter) {
			// echo 'SELECT m FROM Message m ' . $join . $where . $order . " \n";
			$query = $entityManager->createQuery ( 'SELECT m FROM Message m ' . $join . $where . $order );
			// echo 'SELECT m FROM Message m ' . $join . $where . $order ;
			if ($filterStart)
				$query->setFirstResult ( $offset );
			if ($filterLimit)
				$query->setMaxResults ( $maxResults );
			$queryParam = Array ();
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			if (! is_null ( $userID ))
				$queryParam ['userID'] = $userID;
			if (! is_null ( $projectID ))
				$queryParam ['projectID'] = $projectID;
			if (! is_null ( $arrayProject ))
				$queryParam ['arrayProject'] = $arrayProject;
			if (! is_null ( $filterDateFrom )) {
				$filterDateFrom = date_format ( date_create ( $filterDateFrom ), "Y-m-d" );
				$queryParam ['dateFrom'] = "" . $filterDateFrom . "";
			}
			if (! is_null ( $filterDateTo )) {
				$filterDateTo = date_format ( date_create ( $filterDateTo ), "Y-m-d" );
				$queryParam ['dateTo'] = "" . $filterDateTo . "";
			}
			// if (! is_null ( $userIDo1 ))
			// $queryParam ['userIDo1'] = $userIDo1;
			// if (! is_null ( $userIDo2 ))
			// $queryParam ['userIDo2'] = $userIDo2;
			
			if (! is_null ( $filterKeywords )) {
				$queryParam ['keywords'] = "%" . $filterKeywords . "%";
			}
			$query->setParameters ( $queryParam );
			// run
			$messages = $query->getResult ();
			// var_dump ( $messages );
			// exit ();
			return $messages;
		}
		// no filters
		$messages = $entityManager->getRepository ( 'Message' )->findAll ();
		return $messages;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// COUNT
	
	/**
	 *
	 * @param unknown $user        	
	 * @param unknown $filterStatus        	
	 * @return unknown
	 */
	public static function countMessages($user = null, $userFilter = null, $project = null, $projectFilter = null) {
		// filter user from
		// filter project from
		// filter user to
		// filter project to
		return sizeof ( MessageManagementService::getMessages ( $user, $userFilter, $project, $projectFilter ) );
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ONE
	
	/**
	 *
	 * @param long $id        	
	 * @return Message
	 */
	public static function get($id) {
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$message = $entityManager->getRepository ( 'Message' )->find ( $id );
		return $message;
	}
	
	/**
	 * search a $keyWord in messages content
	 *
	 * @param String $keyWord        	
	 * @return Message(s)
	 */
	public static function search($keyWord) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$query = $entityManager->createQuery ( 'SELECT m FROM Message m WHERE m.message LIKE :search' );
		$query->setParameter ( 'search', "%" . $keyWord . "%" );
		$messages = $query->getResult ();
		
		return $messages;
		
		return null;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CREATE
	public static function create($message,   
			/* - from - */
			$fromUser, $fromProject,
			/* - to - */
			 $toUser, $toProject) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// create and save in the database
		$message = new Message ( $fromUser, $fromProject, $toUser, $toProject, $message );
		
		// create
		$entityManager->persist ( $message );
		$entityManager->flush ();
		
		// create new Event
		$action = MessageEvent::$EVENT_TYPE_NEW_MESSAGE;
		if ($fromUser != null && $toUser != null) {
			$action = MessageEvent::$EVENT_TYPE_NEW_MESSAGE_FROM_USER_TO_USER;
		} else if ($fromUser != null && $toProject != null) {
			$action = MessageEvent::$EVENT_TYPE_NEW_MESSAGE_FROM_USER_TO_PROJECT;
		} else if ($fromProject != null && $toUser != null) {
			$action = MessageEvent::$EVENT_TYPE_NEW_MESSAGE_FROM_PROJECT_TO_USER;
		} else if ($fromProject != null && $toProject != null) {
			$action = MessageEvent::$EVENT_TYPE_NEW_MESSAGE_FROM_PROJECT_TO_PROJECT;
		}
		
		// create event
		EventManagementService::createMessageEvent ( $fromUser, $action, $message );
		// send email
		SpecialEventMailler::sendEmailNewMessage ( $message, $toProject );
		
		return $message->getId ();
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// DELETE
	/**
	 *
	 * @param unknown $id        	
	 * @return boolean
	 */
	public static function archive($id, $userSource = null) {
		// TODO set deleted = true
		return true;
	}
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// OTHER
	
	// ...
}