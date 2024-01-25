<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA USER FUNCTIONS
require_once __DIR__ . "/../../vendor/autoload.php";
// Data Model
require_once __DIR__ . "/../../data-model/User.class.php";

require_once __DIR__ . "/../../data-model/Event.class.php";

require_once __DIR__ . "/../../data-model/Project.class.php";
require_once __DIR__ . "/../../data-model/ProjectExtraData.class.php";
require_once __DIR__ . "/../../data-model/Message.class.php";
require_once __DIR__ . "/../../data-model/ThematicCloudWord.class.php";
require_once __DIR__ . "/../../data-model/SubThematicCloudWord.class.php";
require_once __DIR__ . "/../../data-model/MTHplatform.class.php";
// tools
require_once __DIR__ . "/../../api/security/passwordHash.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class UserManagementService
{
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ALL
	/**
	 * Get all users (or filter with $_GET fields)
	 *
	 * @return List of User(s)
	 */
	public static function getUsers()
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// check filters
		$filter = false;
		$filterStart = false;
		$filterLimit = false;

		$where = "";
		$filterDeleted = false;
		$deleted = null;

		if (isset($_GET['start']) && $_GET['start'] != "") { // && is_int ( $_GET ['start'] )
			$filter = true;
			$filterStart = true;
			$offset = intval($_GET['start']);
		}

		if (isset($_GET['limit']) && $_GET['limit'] != "") { // && is_int ( $_GET ['limit'] )
			$filter = true;
			$filterLimit = true;
			$maxResults = intval($_GET['limit']);
		}

		if (isset($_GET['deleted']) && $_GET['deleted'] != "") { // && is_int ( $_GET ['limit'] )
			$filter = true;
			$filterDeleted = true;

			// get deleted value
			if (is_bool($_GET['deleted']))
				$deleted = boolval($_GET['deleted']);
			else {
				if (strtolower($_GET['deleted']) == "true") {
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
			$where .= " u.deleted = :deleted ";
		}

		if (isset($_GET['status']) && $_GET['status'] != "" && $_GET['status'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterStatus = $_GET['status'];
			if ($filterStatus == "not_validated") {
				$where .= " u.status = " . User::$STATUS_NOT_VALIDATED;
			} else if ($filterStatus == "user") {
				$where .= " (u.status = " . User::$STATUS_ACTIVE . " AND u.right = " . User::$RIGHT_USER . ") ";
			} else if ($filterStatus == "projects_manager") {
				$where .= " u.right = " . User::$RIGHT_PROJECT_MANAGER;
			} else if ($filterStatus == "admin") {
				$where .= " u.right = " . User::$RIGHT_ADMIN;
			} else if ($filterStatus == "blocked") {
				$where .= " u.status = " . User::$STATUS_BLOCKED;
			} else if ($filterStatus == "inactive") {
				$where .= " u.status = " . User::$STATUS_INACTIVE;
			}
		}

		// keywords filter (title)
		$filterKeywords = null;
		if (isset($_GET['keywords']) && $_GET['keywords'] != "" && $_GET['keywords'] != "undefined") {
			$filter = true;
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterKeywords = $_GET['keywords'];
			$where .= " (u.email LIKE :keywords1 OR u.firstName LIKE :keywords2 OR u.lastName LIKE :keywords3 ) ";
		}

		$order = " ";
		if (isset($_GET['order']) && $_GET['order'] != "") {
			$filter = true;
			switch (strtolower($_GET['order'])) {
				case "desc":
					$order = " ORDER BY u.id DESC";
					break;
				case "asc":
				default:
					$order = " ORDER BY u.id ASC";
					break;
			}
		}

		if ($filter) {
			$query = $entityManager->createQuery('SELECT u FROM User u ' . $where . $order); // WHERE u.age > 20

			if ($filterStart)
				$query->setFirstResult($offset);
			if ($filterLimit)
				$query->setMaxResults($maxResults);

			$queryParam = array();
			if ($filterDeleted)
				$queryParam['deleted'] = $deleted;
			if (!is_null($filterKeywords)) {
				$queryParam['keywords1'] = "%" . $filterKeywords . "%";
				$queryParam['keywords2'] = "%" . $filterKeywords . "%";
				$queryParam['keywords3'] = "%" . $filterKeywords . "%";
			}
			$query->setParameters($queryParam);
			$users = $query->getResult();
			return $users;
		}

		// no filters
		$users = $entityManager->getRepository('User')->findAll();
		return $users;
	}

	/**
	 * Int $emailReception
	 *
	 * @return User (list of)
	 */
	public static function fetchEmailUsers($emailReception, $filterAlert = null)
	{
		// init
		$entityManager = $GLOBALS['entityManager'];

		// check filters
		$where = "";

		// construct where
		$where .= " WHERE u.deleted = :deleted AND u.emailReception = :emailReception ";
		if (!is_null($filterAlert)) {
			switch ($filterAlert) {
				case "newUser":
					$where .= " AND u.emailAlertNewUserAccount = :filterAlert";
					break;
				case "newProject":
					$where .= " AND u.emailAlertNewProject = :filterAlert";
					break;
			}
		}
		$query = $entityManager->createQuery('SELECT u FROM User u ' . $where); // WHERE u.age > 20

		$queryParam = array();
		$queryParam['deleted'] = false;
		$queryParam['emailReception'] = $emailReception;
		if (!is_null($filterAlert)) {
			$queryParam['filterAlert'] = true;
		}
		$query->setParameters($queryParam);

		$users = $query->getResult();
		return $users;
	}

	/**
	 *
	 * @param unknown $date        	
	 * @return User (list of)
	 */
	public static function fetchNotActiveUsers($date)
	{
		// init
		$entityManager = $GLOBALS['entityManager'];

		// check filters
		$where = "";

		// construct where
		$where .= " WHERE u.deleted = :deleted AND u.lastActivity < :lastActivity AND u.status = :status ";

		$query = $entityManager->createQuery('SELECT u FROM User u ' . $where); // WHERE u.age > 20

		$queryParam = array();
		$queryParam['deleted'] = false;
		$queryParam['lastActivity'] = $date;
		$queryParam['status'] = User::$STATUS_ACTIVE;
		$query->setParameters($queryParam);

		$users = $query->getResult();
		return $users;
	}

	/**
	 *
	 * @return array of User(s) (only admin or PM)
	 */
	public static function getProjectsManagers()
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// check filters
		$filter = false;
		$filterStart = false;
		$filterLimit = false;

		$where = " WHERE u.deleted=false AND u.status='" . User::$STATUS_ACTIVE . "' AND (u.right ='" . User::$RIGHT_ADMIN . "' OR u.right ='" . User::$RIGHT_PROJECT_MANAGER . "' )  ";

		if (isset($_GET['start']) && $_GET['start'] != "") { // && is_int ( $_GET ['start'] )
			$filter = true;
			$filterStart = true;
			$offset = intval($_GET['start']);
		}

		if (isset($_GET['limit']) && $_GET['limit'] != "") { // && is_int ( $_GET ['limit'] )
			$filter = true;
			$filterLimit = true;
			$maxResults = intval($_GET['limit']);
		}

		$order = " ";
		if (isset($_GET['order']) && $_GET['order'] != "") {
			$filter = true;
			switch (strtolower($_GET['order'])) {
				case "desc":
					$order = " ORDER BY u.id DESC";
					break;
				case "asc":
				default:
					$order = " ORDER BY u.id ASC";
					break;
			}
		}

		$query = $entityManager->createQuery('SELECT u FROM User u ' . $where . $order); // WHERE u.age > 20

		if ($filterStart)
			$query->setFirstResult($offset);
		if ($filterLimit)
			$query->setMaxResults($maxResults);

		$users = $query->getResult();
		return $users;
	}

	// //////////////////////////////////////////////////////////////////////////////////////////////
	// COUNT
	public static function countUsers($filterRightOrStatus = null)
	{

		// convert GET to param
		if (is_null($filterRightOrStatus) && isset($_GET['filter']) && $_GET['filter'] != "") {
			$filterRightOrStatus = $_GET['filter'];
		}

		// init
		$entityManager = $GLOBALS['entityManager'];

		// check filters
		$filter = false;

		$where = "";
		$filterDeleted = false;
		$deleted = null;
		$join = "";

		if (isset($_GET['deleted']) && $_GET['deleted'] != "") { // && is_int ( $_GET ['limit'] )
			$filter = true;
			$filterDeleted = true;

			// get deleted value
			if (is_bool($_GET['deleted']))
				$deleted = boolval($_GET['deleted']);
			else {
				if (strtolower($_GET['deleted']) == "true") {
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

		if (!is_null($filterRightOrStatus)) {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}

			// string to int
			if ($filterRightOrStatus . "" == "inactive") {
				$filterRightOrStatus = User::$STATUS_INACTIVE;
			} else if ($filterRightOrStatus . "" == "not_validated") {
				$filterRightOrStatus = User::$STATUS_NOT_VALIDATED;
			} else if ($filterRightOrStatus . "" == "admin") {
				$filterRightOrStatus = User::$RIGHT_ADMIN;
			} elseif ($filterRightOrStatus . "" == "project_manager") {
				$filterRightOrStatus = User::$RIGHT_PROJECT_MANAGER;
			} elseif ($filterRightOrStatus . "" == "user") {
				$filterRightOrStatus = User::$RIGHT_USER;
			} elseif ($filterRightOrStatus . "" == "blocked") {
				$filterRightOrStatus = User::$STATUS_BLOCKED;
			}

			if ($filterRightOrStatus == User::$RIGHT_ADMIN) {
				$where .= " u.right = " . User::$RIGHT_ADMIN;
			} else if ($filterRightOrStatus == User::$RIGHT_PROJECT_MANAGER) {
				$where .= " u.right = " . User::$RIGHT_PROJECT_MANAGER;
			} else if ($filterRightOrStatus == User::$RIGHT_USER) {
				$where .= " (u.right = " . User::$RIGHT_USER . " AND u.status =" . User::$STATUS_ACTIVE . " )";
			} else if ($filterRightOrStatus == User::$STATUS_BLOCKED) {
				$where .= " u.status = " . User::$STATUS_BLOCKED;
			} else if ($filterRightOrStatus == User::$STATUS_INACTIVE) {
				$where .= " u.status = " . User::$STATUS_INACTIVE;
			} else if ($filterRightOrStatus == User::$STATUS_NOT_VALIDATED) {
				$where .= " u.status = " . User::$STATUS_NOT_VALIDATED;
			}
			// else if ($filterRightOrStatus == "archived") {
			// $where .= " u.status = " . User::$STATUS_BLOCKED;
			// }
		}

		// keywords filter (title)
		$filterKeywords = null;
		if (isset($_GET['keywords']) && $_GET['keywords'] != "" && $_GET['keywords'] != "undefined") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$filterKeywords = $_GET['keywords'];
			$where .= " (u.email LIKE :keywords1 OR u.firstName LIKE :keywords2 OR u.lastName LIKE :keywords3 ) ";
		}

		$query = $entityManager->createQuery('SELECT COUNT (u.id) FROM User u ' . $join . $where);
		$queryParam = array();
		if ($filterDeleted)
			$queryParam['deleted'] = $deleted;
		if (!is_null($filterKeywords)) {
			$queryParam['keywords1'] = "%" . $filterKeywords . "%";
			$queryParam['keywords2'] = "%" . $filterKeywords . "%";
			$queryParam['keywords3'] = "%" . $filterKeywords . "%";
		}
		// if ($filterDeleted)
		// $queryParam ['deleted'] = $deleted;
		$query->setParameters($queryParam);
		$projectsCount = $query->getResult();
		// var_dump( $projectsCount[0][1]); exit;
		return $projectsCount[0][1];
	}

	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ONE

	/**
	 *
	 * @param long $id        	
	 * @return User
	 */
	public static function get($id)
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// no filters
		$user = $entityManager->getRepository('User')->find($id);
		return $user;
	}

	/**
	 *
	 * @param String $login        	
	 * @return User
	 */
	public static function getByLogin($login)
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// no filters
		$query = $entityManager->createQuery('SELECT u FROM User u WHERE u.login = :login');
		$query->setParameter('login', $login);
		$user = $query->getResult();
		if (count($user) == 1)
			return $user[0];
		else
			return null;
	}

	/**
	 *
	 * @param String $login        	
	 * @return boolean true if user with this login exists, false otherwise
	 */
	public static function exists($login)
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// no filters
		$query = $entityManager->createQuery('SELECT u FROM User u WHERE u.login = :login');
		$query->setParameter('login', $login);

		$user = $query->getResult();
		if (count($user) == 1)
			return true;
		else
			return false;
	}

	// //////////////////////////////////////////////////////////////////////////////////////////////
	// CREATE
	public static function tryInitLDAP($ldapLogin, $userPassword)
	{

		//
		if (UserManagementService::exists($ldapLogin)) {
			//
			return false;
		} else {
			// init user var
			$userEmail = null;
			$firstName = null;
			$lastName = null;
			$phoneGroup = 33;
			$phoneNumber = null;
			$laboratoryOrCompagny = null;
			$workplaceAddress = null;
			$typeOfLaboratoryOrCompany = null;

			// init ldap
			$connect = ldap_connect(ldap_server);
			$r = ldap_bind($connect);

			// seek uid
			$sr = ldap_search($connect, ldap_filter, ldap_identifier . $ldapLogin);
			$info = ldap_get_entries($connect, $sr);
			for ($i = 0; $i < $info["count"]; $i++) {
				$dn = $info[$i]["dn"];
				$lastName = $info[$i]["sn"][0];
				$firstName = $info[$i]["givenname"][0];
				$phoneNumber = $info[$i]["telephonenumber"][0];
				$userEmail = strtolower($info[$i]["mail"][0]);
				// var_dump ( $info [$i] ["mail"][0] );
			}

			// dn + pwd authentification
			if ($connect) {
				$ValidateUser = @ldap_bind($connect, $dn, $userPassword);
				ldap_close($connect);

				// SUCCESS: add user to database
				if ($ValidateUser) {
					UserManagementService::create($ldapLogin, $userEmail, null, $firstName, $lastName, $phoneGroup, $phoneNumber, $laboratoryOrCompagny, $workplaceAddress, $typeOfLaboratoryOrCompany);
					return true;
				} else {
					return false;
				}
			} else {
				ldap_close($connect);
				return false;
			}
		}
	}

	/**
	 *
	 * @param String $email        	
	 * @param String $password        	
	 * @param String $firstName        	
	 * @param String $lastName        	
	 * @param Short $phoneGroup        	
	 * @param String $phoneNumber        	
	 * @param String $laboratoryOrCompagny        	
	 * @param String $workplaceAddress        	
	 * @param String $typeOfLaboratoryOrCompany        	
	 * @param String $status
	 *        	(optional, default: 'not_validated')
	 * @param String $right
	 *        	(optional, default: 'user')
	 * @return Long
	 */
	public static function create($login, $email, $password, $firstName, $lastName, $phoneGroup, $phoneNumber, $laboratoryOrCompagny, $workplaceAddress, $typeOfLaboratoryOrCompany, $status = "not_validated", $right = "user")
	{

		// status
		$statusInt = User::$STATUS_NOT_VALIDATED;
		switch ($status) {
			case "active":
				$statusInt = User::$STATUS_ACTIVE;
				break;
			case "blocked":
				$statusInt = User::$STATUS_BLOCKED;
				break;
			case "inactive":
				$statusInt = User::$STATUS_INACTIVE;
				break;
			case "not_validated":
			default:
				$statusInt = User::$STATUS_NOT_VALIDATED;
				break;
		}

		// right
		$rightInt = User::$RIGHT_USER;
		switch ($right) {
			case "admin":
				$rightInt = User::$RIGHT_ADMIN;
				break;
			case "project_manager":
				$rightInt = User::$RIGHT_PROJECT_MANAGER;
				break;
			case "user":
			default:
				$rightInt = User::$RIGHT_USER;
				break;
		}

		// pwd
		if ($password != null)
			$password = create_hash($password);

		// init
		$entityManager = $GLOBALS['entityManager'];

		// create and save in the database
		$user = new User($login, $email, $statusInt, $rightInt);

		$user->setPassword($password);
		$user->setFirstName($firstName);
		$user->setLastName($lastName);
		$user->setPhoneGroup($phoneGroup);
		$user->setPhoneNumber($phoneNumber);

		$user->setLaboratoryOrCompagny($laboratoryOrCompagny);
		$user->setWorkplaceAddress($workplaceAddress);
		$user->setLaboratoryType($typeOfLaboratoryOrCompany);

		$entityManager->persist($user);
		$entityManager->flush();

		EventManagementService::createUserEvent($user, UserEvent::$EVENT_TYPE_NEW_USER, $user);

		return $user->getId();
	}

	/**
	 *
	 * @param unknown $email        	
	 * @param unknown $right        	
	 */
	public static function invite($email, $right, $userAdmin)
	{
		$rightInt = User::$RIGHT_USER;
		switch ($right) {
			case "admin":
				$rightInt = User::$RIGHT_ADMIN;
				break;
			case "project_manager":
				$rightInt = User::$RIGHT_PROJECT_MANAGER;
				break;
			case "user":
			default:
				$rightInt = User::$RIGHT_USER;
				break;
		}

		// init
		$entityManager = $GLOBALS['entityManager'];

		// create and save in the database
		$u = new User($email, $email, User::$STATUS_ACTIVE, $rightInt);
		$entityManager->persist($u);
		$entityManager->flush();

		EventManagementService::createUserEvent($userAdmin, UserEvent::$EVENT_TYPE_NEW_USER, $u);
	}

	// //////////////////////////////////////////////////////////////////////////////////////////////
	// UPDATE

	/**
	 *
	 * @param Long $id        	
	 * @param String $password        	
	 * @param String $firstName        	
	 * @param String $lastName        	
	 * @param String $phoneGroup        	
	 * @param String $phoneNumber        	
	 * @param String $laboratoryOrCompagny        	
	 * @param String $workplaceAddress        	
	 * @param String $typeOfLaboratoryOrCompany        	
	 * @param String $emailReception        	
	 * @param boolean $emailAlertNewUserAccount        	
	 * @param boolean $emailAlertNewProject        	
	 * @param boolean $emailAlertNewEventFollowedProject        	
	 * @param boolean $emailAlertNewMessage        	
	 * @param String $mthPlatform (optional)
	 * @param String  $emailLanguage (optional)
	 * @param User  $userSource (optional)
	 * @return boolean true if success, false otherwise
	 */
	public static function update(
		$id,
		$password,
		$firstName,
		$lastName,
		/**/
		$phoneGroup,
		$phoneNumber,
		$laboratoryOrCompagny,
		$workplaceAddress,
		$typeOfLaboratoryOrCompany,
		/**/
		$emailReception,
		$emailAlertNewUserAccount,
		$emailAlertNewProject,
		$emailAlertNewEventFollowedProject,
		$emailAlertNewMessage,
		// new mama#41
		$mthPlatform = null,
		/* other */
		$emailLanguage = "en",
		$userSource = null
	) {

		// init
		$entityManager = $GLOBALS['entityManager'];

		// get user from the database
		$user = UserManagementService::get($id);

		if (is_null($user) || $user->isDeleted())
			return false;

		if (!is_null($password)) {
			$password = create_hash($password);
			$user->setPassword($password);
		}

		$user->setFirstName($firstName);
		$user->setLastName($lastName);
		$user->setPhoneGroup($phoneGroup);
		$user->setPhoneNumber($phoneNumber);

		$user->setLaboratoryOrCompagny($laboratoryOrCompagny);
		$user->setWorkplaceAddress($workplaceAddress);
		$user->setLaboratoryType($typeOfLaboratoryOrCompany);

		$user->setEmailReception($emailReception);
		$user->setEmailAlert($emailAlertNewUserAccount, $emailAlertNewProject, $emailAlertNewEventFollowedProject, $emailAlertNewMessage);
		$user->setEmailLanguage($emailLanguage);

		//mama#41
		$mthPf = $mthPlatform !=null ? $entityManager->getRepository('MTHplatform')->find($mthPlatform) : null;
		$user->setMthPlatform($mthPf);

		$user->setUpdated();
		$entityManager->persist($user);
		$entityManager->flush();

		if (is_null($userSource))
			$userSource = $user;
		EventManagementService::createUserEvent($userSource, UserEvent::$EVENT_TYPE_UPDATE_USER__informations, $user);

		return true;
	}

	public static function updatePasswordCheck($id, $passwordOld, $passwordNew, $userSource = null)
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// get user from the database
		$user = UserManagementService::get($id);

		if (is_null($user) || $user->isDeleted())
			return false;
		if (is_null(UserManagementService::authenticate($user->getLogin(), $passwordOld)))
			return false;

		if (!is_null($passwordNew)) {
			$passwordNew = create_hash($passwordNew);
			$user->setPassword($passwordNew);
		}

		$user->setUpdated();

		$entityManager->persist($user);
		$entityManager->flush();

		if (is_null($userSource))
			$userSource = $user;
		EventManagementService::createUserEvent($userSource, UserEvent::$EVENT_TYPE_UPDATE_USER__password, $user);

		return true;
	}
	public static function resetPassword($email, $passwordNew, $userSource = null)
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// get user from the database
		$user = UserManagementService::getByLogin($email);

		if (is_null($user) || $user->isDeleted())
			return false;

		if (!is_null($passwordNew)) {
			$passwordNew = create_hash($passwordNew);
			$user->setPassword($passwordNew);
		}

		$user->setUpdated();

		$entityManager->persist($user);
		$entityManager->flush();

		if (is_null($userSource))
			$userSource = $user;
		EventManagementService::createUserEvent($userSource, UserEvent::$EVENT_TYPE_UPDATE_USER__reset_password, $user);

		return true;
	}

	/**
	 *
	 * @param unknown $user        	
	 */
	public static function updateObject($user, $isAdmin = false, $userSource = null)
	{
		if (is_null($user))
			return false;

		$event = UserEvent::$EVENT_TYPE_UPDATE_USER__informations;

		$userInDB = null;
		if (is_a($user, "User")) {
			$userInDB = UserManagementService::get($user->getId());

			$userInDB->setFirstName($user->getFirstName());
			$userInDB->setLastName($user->getLastName());
			$userInDB->setPhoneGroup($user->getPhoneGroup());
			$userInDB->setPhoneNumber($user->getPhoneNumber());

			$userInDB->setLaboratoryOrCompagny($user->getLaboratoryOrCompagny());
			$userInDB->setWorkplaceAddress($user->getWorkplaceAddress());
			$userInDB->setLaboratoryType($user->getLaboratoryType());

			$userInDB->setEmailReception($user->getEmailReception());
			$userInDB->setEmailAlert($user->isEmailAlertNewUserAccount(), $user->isEmailAlertNewProject(), $user->isEmailAlertNewEventFollowedProject(), $user->isEmailAlertNewMessage());
			$user->setEmailLanguage($user->getEmailLanguage());

			if ($isAdmin) {
				// set status / right
				$userInDB->setStatus($user->getStatus());
				$userInDB->setRight($user->getRight());
				if ($user->isBlocked()) {
					$event = UserEvent::$EVENT_TYPE_UPDATE_USER__set2blocked;
				} else if ($user->isProjectManager()) {
					$event = UserEvent::$EVENT_TYPE_UPDATE_USER__set2pm;
				} else if ($user->isAdmin()) {
					$event = UserEvent::$EVENT_TYPE_UPDATE_USER__set2admin;
				} else if ($user->isUser()) {
					$event = UserEvent::$EVENT_TYPE_UPDATE_USER__set2user;
				}
			}
		} else { // case of json object
			$userInDB = UserManagementService::get($user['id']);

			// TODO update via JSON
		}
		$userInDB->setUpdated();

		// in db!!!
		$entityManager = $GLOBALS['entityManager'];
		$entityManager->persist($userInDB);
		$entityManager->flush();

		if (is_null($userSource))
			$userSource = $user;
		EventManagementService::createUserEvent($userSource, UserEvent::$EVENT_TYPE_UPDATE_USER__informations, $user);

		return true;
	}
	public static function updateRight($userId, $statusRight, $userSource = null)
	{
		$user = UserManagementService::get($userId);

		if (is_null($user))
			return false;

		$event = UserEvent::$EVENT_TYPE_UPDATE_USER__informations;

		// set status / right
		if ($statusRight == "blocked") {
			$user->setStatus("blocked");
			$event = UserEvent::$EVENT_TYPE_UPDATE_USER__set2blocked;
		} else if ($statusRight == "project_manager") {
			$user->setStatus("active");
			$user->setRight("project_manager");
			$event = UserEvent::$EVENT_TYPE_UPDATE_USER__set2pm;
		} else if ($statusRight == "admin") {
			$user->setStatus("active");
			$user->setRight("admin");
			$event = UserEvent::$EVENT_TYPE_UPDATE_USER__set2admin;
		} else if ($statusRight == "user") {
			$user->setStatus("active");
			$user->setRight("user");
			$event = UserEvent::$EVENT_TYPE_UPDATE_USER__set2user;
		}

		$user->setUpdated();

		// in db!!!
		$entityManager = $GLOBALS['entityManager'];
		$entityManager->persist($user);
		$entityManager->flush();

		if (is_null($userSource))
			$userSource = $user;
		EventManagementService::createUserEvent($userSource, $event, $user);

		return true;
	}

	// TODO confirme user (if was invited, set password and extra data)

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

		// get user from the database
		$user = UserManagementService::get($id);

		if (is_null($user) || $user->isDeleted())
			return false;

		$user->delete();

		$user->setUpdated();

		$entityManager->persist($user);
		$entityManager->flush();

		if (is_null($userSource))
			$userSource = $user;
		EventManagementService::createUserEvent($userSource, UserEvent::$EVENT_TYPE_UPDATE_USER__deleted, $user);

		return true;
	}
	// //////////////////////////////////////////////////////////////////////////////////////////////
	// OTHER
	/**
	 *
	 * @param string $login        	
	 * @param string $password        	
	 * @return User|NULL
	 */
	public static function authenticate($login, $password)
	{

		// init
		$user = null;
		$entityManager = $GLOBALS['entityManager'];

		if (!strpos($login, '@')) { // || preg_match ( ldap_email_filter, $login, $matches )
			$ldapLogin = $login;
			// if (isset ( $matches ) && count ( $matches ) > 0) {
			// $ldapLogin = $matches [1];
			// }

			// init ldap
			$connect = ldap_connect(ldap_server);
			$r = ldap_bind($connect);

			// seek uid
			$sr = ldap_search($connect, ldap_filter, ldap_identifier . $ldapLogin);
			$info = ldap_get_entries($connect, $sr);
			$dn = null;
			for ($i = 0; $i < $info["count"]; $i++) {
				$dn = $info[$i]["dn"];
			}

			// dn + pwd authentification
			if ($connect) {
				$ValidateUser = @ldap_bind($connect, $dn, $password);
				ldap_close($connect);

				// SUCCESS: add user to database
				if ($ValidateUser) {
					$query = $entityManager->createQuery('SELECT u FROM User u WHERE u.login = :login');
					$query->setParameters(array(
						'login' => $ldapLogin
					));
					$user = $query->getResult();
				}
			}
		} else {
			// no ldap
			$query = $entityManager->createQuery('SELECT u FROM User u WHERE u.login = :login AND u.password = :password');
			$query->setParameters(array(
				'login' => $login,
				'password' => create_hash($password)
			));
			$user = $query->getResult();
		}
		if (count($user) == 1)
			return $user[0];
		else
			return null;
	}

	/**
	 *
	 * @param User $user        	
	 * @return boolean
	 */
	public static function setLastActivityNow($user)
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// get user from the database

		if (is_null($user) || $user->isDeleted())
			return false;

		$user->setLastActivity();

		if ($user->isInactive()) {
			$user->setStatus(User::$STATUS_ACTIVE);
		}

		$entityManager->persist($user);
		$entityManager->flush();

		return true;
	}

	/**
	 *
	 * @param User $user        	
	 * @return boolean
	 */
	public static function setToInactive($user)
	{
		// init
		$entityManager = $GLOBALS['entityManager'];

		// get user from the database

		if (is_null($user) || $user->isDeleted())
			return false;

		if ($user->isActive()) {
			$user->setStatus(User::$STATUS_INACTIVE);
		}

		$entityManager->persist($user);
		$entityManager->flush();

		return true;
	}

	/**
	 *
	 * @param unknown $nbWeeks        	
	 */
	public static function setUsersToInactive($nbWeeks, $userSource = null)
	{
		$date = new \DateTime("-" . intval($nbWeeks) . " week");
		$users = UserManagementService::fetchNotActiveUsers($date);
		$count = 0;
		// for each user, inactive them
		foreach ($users as $k => $user) {
			// for each user, -> set status to inactive
			if ($user->isActive()) {
				UserManagementService::setToInactive($user);
				$count++;
				if (!is_null($userSource))
					EventManagementService::createUserEvent($userSource, UserEvent::$EVENT_TYPE_UPDATE_USER__set2inactive, $user);
			}
		}
		return $count;
	}
}
