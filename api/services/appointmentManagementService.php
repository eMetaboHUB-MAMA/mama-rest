<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA PORJECTS FUNCTIONS
require_once __DIR__ . "/../../vendor/autoload.php";
// Data Model
// require_once __DIR__ . "/../../data-model/User.class.php";
require_once __DIR__ . "/../../data-model/Project.class.php";
require_once __DIR__ . "/../../data-model/ProjectExtraData.class.php";
require_once __DIR__ . "/../../data-model/Appointment.class.php";
require_once __DIR__ . "/../../data-model/AppointmentProp.class.php";
require_once __DIR__ . "/../../data-model/AppointmentEvent.class.php";
// require_once __DIR__ . "/../../data-model/ThematicCloudWord.class.php";
// require_once __DIR__ . "/../../data-model/MTHplatform.class.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class AppointmentManagementService {
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ALL
	/**
	 * Get all appointments (or filter with $_GET fields)
	 *
	 * @return List of Appointment(s)
	 */
	public static function getAppointments($user = null, $userFilter = null, $project = null, $dateFilter = null) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// check filters
		$filter = false;
		$filterStart = false;
		$filterLimit = false;
		
		// extra filter first
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
		
		$where = "";
		$whereFilter = "";
		$filterDeleted = false;
		$deleted = null;
		$join = "";
		
		// filter on a user
		$userID = null;
		$userID2 = null;
		if (! is_null ( $user )) {
			$filter = true;
			if ($userFilter != null && $userFilter == "from")
				$whereFilter .= " a.fromUser = :userID ";
			else if ($userFilter != null && $userFilter == "to")
				$whereFilter .= " ( a.toUser = :userID ) ";
			else {
				$whereFilter .= " (a.fromUser = :userID OR a.toUser = :userID2 ) ";
				$userID2 = $user;
			}
			$userID = $user;
			// if ($noUser)
			// $userID = - 1;
		}
		
		// filter on a project
		$projectID = null;
		if (! is_null ( $project )) {
			$filter = true;
			// construct where
			if ($whereFilter == "") {
				$whereFilter = " ( ";
			} else {
				$whereFilter .= " OR ( ";
			}
			$join .= " JOIN a.project p ";
			
			$whereFilter .= " ( p.id = :projectID ) ";
			$whereFilter .= " ) ";
			// $userID = $user;
			$projectID = $project;
		}
		
		$dateNow = null;
		if (! is_null ( $dateFilter )) {
			$filter = true;
			// construct where
			if ($whereFilter == "") {
				$whereFilter = " ( ";
			} else {
				$whereFilter .= " AND ( ";
			}
			$symbnole = " ";
			if ($dateFilter == "past") {
				$symbnole = " < ";
			} else if ($dateFilter == "ongoing") {
				$symbnole = " > ";
			}
			$whereFilter .= " ( a.appointmentDate IS NOT NULL AND a.appointmentDate " . $symbnole . " :dateNow ) ";
			$dateNow = new \DateTime ( "now" );
			// close
			$whereFilter .= " ) ";
		}
		$dateAppTo = null;
		if (isset ( $_GET ['app_to'] ) && $_GET ['app_to'] != "" && $_GET ['app_to'] != "undefined") {
			$filter = true;
			// construct where
			if ($whereFilter == "") {
				$whereFilter = " ( ";
			} else {
				$whereFilter .= " AND ( ";
			}
			$whereFilter .= " ( a.appointmentDate IS NOT NULL AND a.appointmentDate < :dateAppTo ) ";
			$dateAppTo = $_GET ['app_to'] ;
			// close
			$whereFilter .= " ) ";
		}
		$dateAppFrom = null;
		if (isset ( $_GET ['app_from'] ) && $_GET ['app_from'] != "" && $_GET ['app_from'] != "undefined") {
			$filter = true;
			// construct where
			if ($whereFilter == "") {
				$whereFilter = " ( ";
			} else {
				$whereFilter .= " AND ( ";
			}
			$whereFilter .= " ( a.appointmentDate IS NOT NULL AND a.appointmentDate > :dateAppFrom ) ";
			$dateAppFrom = $_GET ['app_from'] ;
			// close
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
			$where .= " a.deleted = :deleted ";
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
			$where .= " (a.created >= :dateFrom ) ";
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
			$where .= " (a.created <= :dateTo ) ";
		}
		
		// keywords filter (appointment message)
		$filterKeywords = null;
		if (isset ( $_GET ['keywords'] ) && $_GET ['keywords'] != "" && $_GET ['keywords'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterKeywords = $_GET ['keywords'];
			$where .= " (a.message LIKE :keywords ) ";
		}
		
		$order = " ";
		if (isset ( $_GET ['order'] ) && $_GET ['order'] != "") {
			$filter = true;
			switch (strtolower ( $_GET ['order'] )) {
				case "desc" :
					$order = " ORDER BY a.id DESC";
					break;
				case "asc" :
				default :
					$order = " ORDER BY a.id ASC";
					break;
			}
		}
		if ($filter) {
			//echo 'SELECT m FROM Appointment m ' . $join . $where . $order . "\n";
			$query = $entityManager->createQuery ( 'SELECT a FROM Appointment a ' . $join . $where . $order );
			// echo 'SELECT a FROM Appointment a ' . $join . $where . $order ; exit;
			// var_dump($userID);
			
			// echo 'SELECT m FROM Appointment m ' . $join . $where . $order ;
			if ($filterStart)
				$query->setFirstResult ( $offset );
			if ($filterLimit)
				$query->setMaxResults ( $maxResults );
			$queryParam = Array ();
			if ($filterDeleted)
				$queryParam ['deleted'] = $deleted;
			if (! is_null ( $userID ))
				$queryParam ['userID'] = $userID;
			if (! is_null ( $userID2 ))
				$queryParam ['userID2'] = $userID2;
			if (! is_null ( $projectID ))
				$queryParam ['projectID'] = $projectID;
			if (! is_null ( $dateNow ))
				$queryParam ['dateNow'] = $dateNow;
				// if (! is_null ( $arrayProject ))
				// $queryParam ['arrayProject'] = $arrayProject;
			if (! is_null ( $filterDateFrom )) {
				$filterDateFrom = date_format ( date_create($filterDateFrom), "Y-m-d" );
				$queryParam ['dateFrom'] = "" . $filterDateFrom . "";
			}
			if (! is_null ( $filterDateTo )) {
				$filterDateTo = date_format ( date_create($filterDateTo), "Y-m-d" );
				$queryParam ['dateTo'] = "" . $filterDateTo . "";
			}
			if (! is_null ( $dateAppFrom )) {
				$queryParam ['dateAppFrom'] = "" . $dateAppFrom . "";
			}
			if (! is_null ( $dateAppTo )) {
				$queryParam ['dateAppTo'] = "" . $dateAppTo . "";
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
			$appointments = $query->getResult ();
			// var_dump ( sizeof ( $projects ) );
			// exit ();
			return $appointments;
		}
		// no filters
		$appointments = $entityManager->getRepository ( 'Appointment' )->findAll ();
		return $appointments;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// COUNT
	
	/**
	 *
	 * @param unknown $user        	
	 * @param unknown $filterStatus        	
	 * @return unknown
	 */
	public static function countAppointments($user = null, $userFilter = null, $project = null, $dateFilter = null) {
		return sizeof ( AppointmentManagementService::getAppointments ( $user, $userFilter, $project, $dateFilter ) );
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ONE
	
	/**
	 *
	 * @param long $id        	
	 * @return Appointment
	 */
	public static function get($id) {
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$appointment = $entityManager->getRepository ( 'Appointment' )->find ( $id );
		return $appointment;
	}
	
	/**
	 *
	 * @param long $id        	
	 * @return AppointmentProp
	 */
	public static function getAppointmentProposition($id) {
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$appointment = $entityManager->getRepository ( 'AppointmentProp' )->find ( $id );
		return $appointment;
	}
	
	/**
	 * search a $keyWord in appointments content
	 *
	 * @param String $keyWord        	
	 * @return Appointment(s)
	 */
	public static function search($keyWord) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// no filters
		$query = $entityManager->createQuery ( 'SELECT m FROM Appointment m WHERE m.message LIKE :search' );
		$query->setParameter ( 'search', "%" . $keyWord . "%" );
		$appointments = $query->getResult ();
		
		return $appointments;
		
		return null;
	}
	
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CREATE
	public static function create($message,   
			/* - about - */
			$project,
			/* - from - */
			$fromUser,
			/* - to - */
			$toUser,
			/* - dates - */
			$dates) {
		
		// init
		$entityManager = $GLOBALS ['entityManager'];
		
		// create and save in the database
		$appointment = new Appointment ( $project, $fromUser, $toUser, null, $message );
		
		$entityManager->persist ( $appointment );
		$entityManager->flush ();
		
		// save proposition date
		$arrayNewDatesProp = Array ();
		foreach ( $dates as $key => $value ) {
			$newDateProp = new AppointmentProp ( \DateTime::createFromFormat ( 'Y-m-d H:i:s', $value ) );
			$entityManager->persist ( $newDateProp );
			$entityManager->flush ();
			array_push ( $arrayNewDatesProp, $newDateProp->getId () );
		}
		
		// save link appointment => proposition dates
		$appointmentInDB = $entityManager->getRepository ( 'Appointment' )->find ( $appointment->getId () );
		foreach ( $arrayNewDatesProp as $key => $newDatesId ) {
			$appointmentPropInDB = $entityManager->getRepository ( 'AppointmentProp' )->find ( $newDatesId );
			$appointmentPropInDB->getAppointment ()->add ( $appointmentInDB );
		}
		
		$entityManager->persist ( $appointmentInDB );
		$entityManager->flush ();
		
		// init create new Event
		$action = AppointmentEvent::$EVENT_TYPE_NEW_APPOINTMENT;
		
		// create event
		EventManagementService::createAppointmentEvent ( $fromUser, $action, $appointment );
		// send email
		SpecialEventMailler::sendEmailNewAppointment ( $appointment, $project );
		
		return $appointment->getId ();
	}
	// public static function addPropositionDate($appointment, $date) {
	
	// // init
	// $entityManager = $GLOBALS ['entityManager'];
	
	// // foreach ( $dates as $key => $value ) {
	// $newDateProp = new AppointmentProp ( \DateTime::createFromFormat ( 'Y-m-d H:i:s', $date ) );
	// $newDateProp->setAppointment ( $appointment );
	// $entityManager->persist ( $newDateProp );
	// $entityManager->persist ( $appointment );
	// // }
	// $entityManager->flush ();
	// return true;
	// }
	
	/**
	 *
	 * @param unknown $appointment        	
	 */
	public static function updateObject($appointment, $isAdmin = false, $userSource = null) {
		if (is_null ( $appointment ))
			return false;
		
		$appointmentInDB = null;
		$updated = false;
		$updateBasicData = false;
		$entityManager = $GLOBALS ['entityManager'];
		
		if (is_a ( $appointment, "Appointment" )) {
			$appointmentInDB = AppointmentManagementService::get ( $appointment->getId () );
			
			if ($appointmentInDB->getAppointmentDatesPropositions () != $appointment->getAppointmentDatesPropositions ()) {
				$hql_drop = "DELETE FROM `appointment_propositions_dates` WHERE `appointment_id` = " . intval ( $appointmentInDB->getId () ) . " ";
				$entityManager->getConnection ()->executeUpdate ( $hql_drop );
				$updated = true;
				if ($userSource != null) {
					$countAccepted = 0;
					$countRejected = 0;
					$dateSelected = null;
					$event = Event::$EVENT_TYPE_UPDATE_APPOINTMENT;
					foreach ( $appointment->getAppointmentDatesPropositions () as $k => $appProp ) {
						// $appProp->getProjectsInvolded ()->add ( $appointmentInDB );
						if (! is_null ( $appProp->getAppointmentSelected () )) {
							if ($appProp->getAppointmentSelected ()) {
								$countAccepted ++;
								$dateSelected = $appProp->getAppointmentPropositionDate ();
							} else {
								$countRejected ++;
							}
						}
						$appointmentPropInDB = $entityManager->getRepository ( 'AppointmentProp' )->find ( $appProp->getId () );
						$appointmentPropInDB->setAppointmentSelected ( $appProp->getAppointmentSelected () );
						$appointmentPropInDB->getAppointment ()->add ( $appointmentInDB );
					}
					if ($countAccepted == 1 && sizeof ( $appointment->getAppointmentDatesPropositions () ) == ($countAccepted + $countRejected)) {
						$appointmentInDB->setAppointmentDate ( $dateSelected );
						$event = Event::$EVENT_TYPE_LOCK_APPOINTMENT;
					} else if (sizeof ( $appointment->getAppointmentDatesPropositions () ) == ($countRejected)) {
						$event = Event::$EVENT_TYPE_FAIL_APPOINTMENT;
					}
					EventManagementService::createAppointmentEvent ( $userSource, $event, $appointmentInDB );
				}
			}
		} else { // case of json object
			$appointmentInDB = AppointmentManagementService::get ( $appointment ['id'] );
			
			// TODO update via JSON
		}
		
		// in db!!!
		if ($updated) {
			$appointmentInDB->setUpdated ();
			$entityManager->persist ( $appointmentInDB );
			$entityManager->flush ();
		}
		
		// create new Event
		if ($userSource != null && $updateBasicData) {
			// EventManagementService::createProjectEvent ( $userSource, Event::$EVENT_TYPE_UPDATE_PROJECT__informations, $appointmentInDB );
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
	public static function archive($id, $userSource = null) {
		// TODO set deleted = true
		return true;
	}
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// OTHER
	
	// ...
}