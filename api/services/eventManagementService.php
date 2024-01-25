<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA PORJECTS FUNCTIONS
require_once __DIR__ . "/../../vendor/autoload.php";
// Data Model
require_once __DIR__ . "/../../data-model/Event.class.php";
require_once __DIR__ . "/../../data-model/AdminEvent.class.php";
require_once __DIR__ . "/../../data-model/ProjectEvent.class.php";
require_once __DIR__ . "/../../data-model/UserEvent.class.php";
require_once __DIR__ . "/../../data-model/MessageEvent.class.php";
require_once __DIR__ . "/../../data-model/AppointmentEvent.class.php";
// ext DM
require_once __DIR__ . "/../../data-model/User.class.php";
require_once __DIR__ . "/../../data-model/Project.class.php";
require_once __DIR__ . "/../../data-model/ProjectExtraData.class.php";
require_once __DIR__ . "/../../data-model/Message.class.php";
require_once __DIR__ . "/../../data-model/ThematicCloudWord.class.php";
require_once __DIR__ . "/../../data-model/SubThematicCloudWord.class.php";
require_once __DIR__ . "/../../data-model/MTHplatform.class.php";

// //////////////////////////////////////////////////////////////////////////////////////////////
/**
 *
 * @param Event $a        	
 * @param Event $b        	
 * @return number
 */
function sortByEventsIds($a, $b) {
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
class EventManagementService {
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ALL
	/**
	 * Get all events (or filter with $_GET fields)
	 *
	 * @return List of Event(s)
	 */
	public static function getEvents($user = null, $project = null, $isAdmin = false) {
		$events = array ();
		
		// meuh
		$loadUsers = true;
		$loadProjects = true;
		$loadAdmin = true;
		$loadMessage = true;
		$loadAppointments = true;
		
		if (isset ( $_GET ['filter'] )) {
			switch (strtolower ( $_GET ['filter'] )) {
				case "users" :
					$loadProjects = false;
					$loadAdmin = false;
					$loadMessage = false;
					$loadAppointments = false;
					break;
				case "projects" :
					$loadUsers = false;
					$loadAdmin = false;
					$loadMessage = false;
					$loadAppointments = false;
					break;
				case "admin" :
					$loadUsers = false;
					$loadProjects = false;
					$loadMessage = false;
					$loadAppointments = false;
					break;
				case "message" :
					$loadUsers = false;
					$loadProjects = false;
					$loadAdmin = false;
					$loadAppointments = false;
					break;
				case "appointments" :
					$loadUsers = false;
					$loadProjects = false;
					$loadAdmin = false;
					$loadMessage = false;
					break;
			}
		}
		
		$userEvents = Array ();
		$projectsEvents = Array ();
		$adminEvents = Array ();
		$messagesEvents = Array ();
		$appointmentsEvents = Array ();
		
		// Users events
		if ($loadUsers)
			$userEvents = EventManagementService::getUsersEvents ( $user );
			
			// Projects events
		if ($loadProjects)
			$projectsEvents = EventManagementService::getProjectsEvents ( $user, $project );
			
			// Admin events
		if ($loadAdmin && $isAdmin)
			$adminEvents = EventManagementService::getAdminEvents ();
			
			// TODO Message events
			
		// Appointment events
		if ($loadAppointments)
			$appointmentsEvents = EventManagementService::getAppointmentsEvents ( $user, $project );
		
		if ($loadUsers)
			$events = array_merge ( $events, $userEvents );
		if ($loadProjects)
			$events = array_merge ( $events, $projectsEvents );
		if ($loadAdmin)
			$events = array_merge ( $events, $adminEvents );
		if ($loadAppointments)
			$events = array_merge ( $events, $appointmentsEvents );
			
			// sort by ID or DATE (th: ID is OK)
		if (isset ( $_GET ['order'] ) && $_GET ['order'] != "") {
			$filter = true;
			switch (strtolower ( $_GET ['order'] )) {
				case "desc" :
					usort ( $events, "sortByEventsIds" );
					$events = array_reverse ( $events );
					break;
				case "asc" :
				default :
					usort ( $events, "sortByEventsIds" );
					break;
			}
		}
		
		// FILTER OFFSET / MAX RESULTS
		if (isset ( $_GET ['start'] ) && $_GET ['start'] != "") { // && is_int ( $_GET ['start'] )
			$offset = intval ( $_GET ['start'] );
			// if ($offset <= sizeof ( $events ))
			$events = array_slice ( $events, $offset );
		}
		if (isset ( $_GET ['limit'] ) && $_GET ['limit'] != "") { // && is_int ( $_GET ['limit'] )
			$maxResults = intval ( $_GET ['limit'] );
			// if ($maxResults >= sizeof ( $events ))
			$events = array_slice ( $events, 0, $maxResults );
		}
		
		return $events;
	}
	
	/**
	 * Get all UserEvent(s) (or filter with $_GET fields)
	 *
	 * @param Long $user
	 *        	the ID of the user to filer (if null return ALL)
	 * @return List of UserEvent(s)
	 */
	public static function getUsersEvents($user = null) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// check filters
		$filter = false;
		// $filterStart = false;
		// $filterLimit = false;
		
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
			$join .= " JOIN p.eventUser u  ";
			$where .= " u.id = :userID ";
			$userID = $user;
		}
		
		// if (isset ( $_GET ['start'] ) && $_GET ['start'] != "") { // && is_int ( $_GET ['start'] )
		// $filter = true;
		// $filterStart = true;
		// $offset = intval ( $_GET ['start'] );
		// }
		
		// if (isset ( $_GET ['limit'] ) && $_GET ['limit'] != "") { // && is_int ( $_GET ['limit'] )
		// $filter = true;
		// $filterLimit = true;
		// $maxResults = intval ( $_GET ['limit'] );
		// }
		
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
		
		// $order = " ";
		// if (isset ( $_GET ['order'] ) && $_GET ['order'] != "") {
		// $filter = true;
		// switch (strtolower ( $_GET ['order'] )) {
		// case "desc" :
		// $order = " ORDER BY p.id DESC";
		// break;
		// case "asc" :
		// default :
		// $order = " ORDER BY p.id ASC";
		// break;
		// }
		// }
		
		if ($filter) {
			$query = $entityManager->createQuery ( 'SELECT p FROM UserEvent p ' . $join . $where );
			$queryParam = Array ();
			if (! is_null ( $userID ))
				$queryParam ['userID'] = $userID;
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			if (! is_null ( $filterDateFrom )) {
				$filterDateFrom = date_format ( date_create ( $filterDateFrom ), "Y-m-d" );
				$queryParam ['dateFrom'] = "" . $filterDateFrom . "";
			}
			if (! is_null ( $filterDateTo )) {
				$filterDateTo = date_format ( date_create ( $filterDateTo ), "Y-m-d" );
				$queryParam ['dateTo'] = "" . $filterDateTo . "";
			}
			$query->setParameters ( $queryParam );
			// if (! is_null ( $userID )) {
			// $query->setParameter ( 'userID', $userID );
			// }
			// if ($filterStart)
			// $query->setFirstResult ( $offset );
			// if ($filterLimit)
			// $query->setMaxResults ( $maxResults );
			// if ($filterDeleted)
			// $query->setParameter ( 'deleted', $deleted );
			// if ($filterDeleted && ! is_null ( $userID )) {
			// $query->setParameters ( array (
			// 'deleted' => $deleted,
			// 'userID' => $userID
			// ) );
			// }
			$events = $query->getResult ();
			// var_dump ( sizeof ( $events ) );
			// exit ();
			return $events;
		}
		
		// no filters
		$events = $entityManager->getRepository ( 'UserEvent' )->findAll ();
		return $events;
	}
	
	/**
	 * Get all UserEvent(s) (or filter with $_GET fields)
	 *
	 * @param Long $user
	 *        	the ID of the user to filer (if null return ALL)
	 * @return List of UserEvent(s)
	 */
	public static function getProjectsEvents($user = null, $project = null) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// check filters
		$filter = false;
		// $filterStart = false;
		// $filterLimit = false;
		
		$where = "";
		$filterDeleted = false;
		$deleted = null;
		$join = "";
		
		$userID = null;
		if (! is_null ( $user )) {
			$filter = true;
			// construct where
			if ($where == "") {
				$where = " WHERE ( ";
			} else {
				$where .= " OR ";
			}
			$join .= " JOIN p.user u  ";
			$where .= " u.id = :userID ";
			$userID = $user;
		}
		
		$projectID = null;
		if (! is_null ( $project )) {
			$filter = true;
			// construct where
			if ($where == "") {
				$where = " WHERE ( ";
			} else {
				$where .= " AND ";
			}
			$join .= " JOIN p.project pr  ";
			$where .= " pr.id = :projectID ";
			$projectID = $project;
		}
		
		// if (isset ( $_GET ['start'] ) && $_GET ['start'] != "") { // && is_int ( $_GET ['start'] )
		// $filter = true;
		// $filterStart = true;
		// $offset = intval ( $_GET ['start'] );
		// }
		
		// if (isset ( $_GET ['limit'] ) && $_GET ['limit'] != "") { // && is_int ( $_GET ['limit'] )
		// $filter = true;
		// $filterLimit = true;
		// $maxResults = intval ( $_GET ['limit'] );
		// }
		
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
				$where .= " ) AND ";
			}
			$where .= " p.deleted = :deleted ";
		} else if ($where != "") {
			$where .= " ) ";
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
		
		// $order = " ";
		// if (isset ( $_GET ['order'] ) && $_GET ['order'] != "") {
		// $filter = true;
		// switch (strtolower ( $_GET ['order'] )) {
		// case "desc" :
		// $order = " ORDER BY p.id DESC";
		// break;
		// case "asc" :
		// default :
		// $order = " ORDER BY p.id ASC";
		// break;
		// }
		// }
		
		if ($filter) {
			$query = $entityManager->createQuery ( 'SELECT p FROM ProjectEvent p ' . $join . $where );
			// if (! is_null ( $userID )) {
			// $query->setParameter ( 'userID', $userID );
			// }
			$queryParam = Array ();
			if (! is_null ( $userID ))
				$queryParam ['userID'] = $userID;
			if (! is_null ( $projectID ))
				$queryParam ['projectID'] = $projectID;
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			if (! is_null ( $filterDateFrom )) {
				$filterDateFrom = date_format ( date_create ( $filterDateFrom ), "Y-m-d" );
				$queryParam ['dateFrom'] = "" . $filterDateFrom . "";
			}
			if (! is_null ( $filterDateTo )) {
				$filterDateTo = date_format ( date_create ( $filterDateTo ), "Y-m-d" );
				$queryParam ['dateTo'] = "" . $filterDateTo . "";
			}
			$query->setParameters ( $queryParam );
			
			// if ($filterStart)
			// $query->setFirstResult ( $offset );
			// if ($filterLimit)
			// $query->setMaxResults ( $maxResults );
			
			// if ($filterDeleted)
			// $query->setParameter ( 'deleted', $deleted );
			// if ($filterDeleted && ! is_null ( $userID )) {
			// $query->setParameters ( array (
			// 'deleted' => $deleted,
			// 'userID' => $userID,
			// 'projectID' => $projectID
			// ) );
			// }
			$events = $query->getResult ();
			// var_dump ( sizeof ( $events ) );
			// exit ();
			return $events;
		}
		
		// no filters
		$events = $entityManager->getRepository ( 'ProjectEvent' )->findAll ();
		return $events;
	}
	public static function getAdminEvents() {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// check filters
		$filter = false;
		
		$where = "";
		$filterDeleted = false;
		$deleted = null;
		$join = "";
		
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
			$where .= " e.deleted = :deleted ";
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
			$where .= " (e.created > :dateFrom ) ";
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
			$where .= " (e.created < :dateTo ) ";
		}
		
		if ($filter) {
			$query = $entityManager->createQuery ( 'SELECT e FROM AdminEvent e ' . $join . $where );
			$queryParam = Array ();
			// if (! is_null ( $userID ))
			// $queryParam ['userID'] = $userID;
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			if (! is_null ( $filterDateFrom )) {
				$filterDateFrom = date_format ( date_create ( $filterDateFrom ), "Y-m-d" );
				$queryParam ['dateFrom'] = "" . $filterDateFrom . "";
			}
			if (! is_null ( $filterDateTo )) {
				$filterDateTo = date_format ( date_create ( $filterDateTo ), "Y-m-d" );
				$queryParam ['dateTo'] = "" . $filterDateTo . "";
			}
			$query->setParameters ( $queryParam );
			$events = $query->getResult ();
			return $events;
		}
		
		// no filters
		$events = $entityManager->getRepository ( 'AdminEvent' )->findAll ();
		return $events;
	}
	public static function getAppointmentsEvents($user = null, $project = null) {
		
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
				$where = " WHERE ( ";
			} else {
				$where .= " OR ";
			}
			$join .= " JOIN p.user u  ";
			$where .= " u.id = :userID ";
			$userID = $user;
		}
		
		$projectID = null;
		if (! is_null ( $project )) {
			$filter = true;
			// construct where
			if ($where == "") {
				$where = " WHERE ( ";
			} else {
				$where .= " AND ";
			}
			$join .= " JOIN p.project pr  ";
			$where .= " pr.id = :projectID ";
			$projectID = $project;
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
				$where .= " ) AND ";
			}
			$where .= " p.deleted = :deleted ";
		} else if ($where != "") {
			$where .= " ) ";
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
		
		if ($filter) {
			$query = $entityManager->createQuery ( 'SELECT p FROM AppointmentEvent p ' . $join . $where );
			// if (! is_null ( $userID )) {
			// $query->setParameter ( 'userID', $userID );
			// }
			$queryParam = Array ();
			if (! is_null ( $userID ))
				$queryParam ['userID'] = $userID;
			if (! is_null ( $projectID ))
				$queryParam ['projectID'] = $projectID;
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			if (! is_null ( $filterDateFrom )) {
				$filterDateFrom = date_format ( date_create ( $filterDateFrom ), "Y-m-d" );
				$queryParam ['dateFrom'] = "" . $filterDateFrom . "";
			}
			if (! is_null ( $filterDateTo )) {
				$filterDateTo = date_format ( date_create ( $filterDateTo ), "Y-m-d" );
				$queryParam ['dateTo'] = "" . $filterDateTo . "";
			}
			$query->setParameters ( $queryParam );
			$events = $query->getResult ();
			// var_dump ( sizeof ( $events ) );
			// exit ();
			return $events;
		}
		
		// no filters
		$events = $entityManager->getRepository ( 'AppointmentEvent' )->findAll ();
		return $events;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// COUNT
	
	// /**
	// *
	// * @param unknown $user
	// * @param unknown $filterType
	// * @return unknown
	// */
	// public static function countEvents($user = null, $filterType = null, $userFilter = null) {
	
	// // convert GET to param
	// if (is_null ( $filterType ) && isset ( $_GET ['filter'] ) && $_GET ['filter'] != "") {
	// $filterType = $_GET ['filter'];
	// }
	
	// // init
	// $entityManager = $GLOBALS ['entityManager'];
	
	// // check filters
	// $filter = false;
	
	// $where = "";
	// $filterDeleted = false;
	// $deleted = null;
	// $join = "";
	
	// $userID = null;
	// if (! is_null ( $user )) {
	// $filter = true;
	// // construct where
	// if ($where == "") {
	// $where = " WHERE ";
	// } else {
	// $where .= " AND ";
	// }
	// if ($userFilter != null && $userFilter == "owner")
	// $join .= " JOIN p.owner u ";
	// else if ($userFilter != null && $userFilter == "inCharge")
	// $join .= " JOIN p.analystInCharge u ";
	// else
	// $join .= " JOIN p.owner u ";
	// $where .= " u.id = :userID ";
	// $userID = $user;
	// }
	
	// if (isset ( $_GET ['deleted'] ) && $_GET ['deleted'] != "") { // && is_int ( $_GET ['limit'] )
	// $filter = true;
	// $filterDeleted = true;
	
	// // get deleted value
	// if (is_bool ( $_GET ['deleted'] ))
	// $deleted = boolval ( $_GET ['deleted'] );
	// else {
	// if (strtolower ( $_GET ['deleted'] ) == "true") {
	// $deleted = true;
	// } else {
	// $deleted = false;
	// }
	// }
	
	// // construct where
	// if ($where == "") {
	// $where = " WHERE ";
	// } else {
	// $where .= " AND ";
	// }
	// $where .= " p.deleted = :deleted ";
	// }
	
	// if (! is_null ( $filterType )) {
	// if ($where == "") {
	// $where = " WHERE ";
	// } else {
	// $where .= " AND ";
	// }
	// // // * -1: rejected
	// // // * 0: waiting
	// // // * 1: completed
	// // // * 2: assigned
	// // // * 3: running
	// // // * 6: blocked
	// // // * 10: archived
	// // if ($filterType == "rejected") {
	// // $where .= " p.status = " . Event::$AD_STATUS_REJECTED;
	// // } else if ($filterType == "waiting") {
	// // $where .= " p.status = " . Event::$AD_STATUS_WAITING;
	// // } else if ($filterType == "completed") {
	// // $where .= " p.status = " . Event::$AD_STATUS_COMPLETED;
	// // } else if ($filterType == "assigned") {
	// // $where .= " p.status = " . Event::$AD_STATUS_ASSIGNED;
	// // } else if ($filterType == "running") {
	// // $where .= " p.status = " . Event::$AD_STATUS_RUNNING;
	// // } else if ($filterType == "blocked") {
	// // $where .= " p.status = " . Event::$AD_STATUS_BLOCKED;
	// // } else if ($filterType == "archived") {
	// // $where .= " p.status = " . Event::$AD_STATUS_ARCHIVED;
	// // }
	// }
	
	// $query = $entityManager->createQuery ( 'SELECT COUNT (p.id) FROM Event p ' . $join . $where );
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
	// $eventsCount = $query->getResult ();
	// // var_dump( $eventsCount[0][1]); exit;
	// return $eventsCount [0] [1];
	// }
	
	// // //////////////////////////////////////////////////////////////////////////////////////////////
	// // GET ONE
	
	// /**
	// *
	// * @param long $id
	// * @return Event
	// */
	// public static function get($id) {
	
	// // init
	// $entityManager = $GLOBALS ['entityManager'];
	
	// // no filters
	// $event = $entityManager->getRepository ( 'Event' )->find ( $id );
	// return $event;
	// }
	
	// /**
	// * search a $keyWord in events title
	// *
	// * @param String $keyWord
	// * @return Event(s)
	// */
	// public static function search($keyWord) {
	
	// // init
	// $entityManager = $GLOBALS ['entityManager'];
	
	// // no filters
	// $query = $entityManager->createQuery ( 'SELECT p FROM Event p WHERE p.title LIKE :search' );
	// $query->setParameter ( 'search', "%" . $keyWord . "%" );
	// $events = $query->getResult ();
	
	// return $events;
	// }
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CREATE
	public static function createProjectEvent($user, $action, $project) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// create and save in the database
		$event = new ProjectEvent ( $user, $action, $project );
		
		$event->setUpdated ();
		
		$entityManager->persist ( $event );
		$entityManager->flush ();
		
		return $event->getId ();
	}
	public static function createUserEvent($user, $action, $userEvent) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// create and save in the database
		$event = new UserEvent ( $user, $action, $userEvent );
		
		$event->setUpdated ();
		
		$entityManager->persist ( $event );
		$entityManager->flush ();
		
		return $event->getId ();
	}
	public static function createAdminEvent($user, $action, $oldName, $newName) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// create and save in the database
		$event = new AdminEvent ( $user, $action, $oldName, $newName );
		
		$event->setUpdated ();
		
		$entityManager->persist ( $event );
		$entityManager->flush ();
		
		return $event->getId ();
	}
	/**
	 *
	 * @param unknown $user        	
	 * @param unknown $action        	
	 * @param unknown $message        	
	 * @return Long
	 */
	public static function createMessageEvent($user, $action, $message) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// create and save in the database
		$event = new MessageEvent ( $user, $action, $message );
		
		$event->setUpdated ();
		
		$entityManager->persist ( $event );
		$entityManager->flush ();
		
		return $event->getId ();
	}
	/**
	 *
	 * @param unknown $user        	
	 * @param unknown $action        	
	 * @param unknown $message        	
	 * @return Long
	 */
	public static function createAppointmentEvent($user, $action, $appointment) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// create and save in the database
		$event = new AppointmentEvent ( $user, $action, $appointment );
		
		$event->setUpdated ();
		
		$entityManager->persist ( $event );
		$entityManager->flush ();
		
		return $event->getId ();
	}
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// OTHER
	
	// ...
}