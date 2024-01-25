<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// Database connection
require_once __DIR__ . "/../../vendor/autoload.php";

// Data Model
require_once __DIR__ . "/../../data-model/User.class.php";

enum Trigger
{
	case Warn;
	case Action;
}

/**
 * Class used to process GDPR query: detect users that can be anonymized, call anonymization methods
 * @author Nils Paulhe
 *        
 */
class GdprManagementService
{


	private static $TRIGGER_WARN = '-5 year';
	private static $TRIGGER_ACTION = '-61 month'; // 5 years and one month

	// //////////////////////////////////////////////////////////////////////////////////////////////
	// GET ALL

	/**
	 * Get all users that can be anonymized
	 *
	 * @return User[], a list of users
	 */
	public static function getUsersThatCanBeAnonymized(Trigger $trigger = Trigger::Warn)
	{
		// init
		$entityManager = $GLOBALS['entityManager'];
		$join = "";
		$where = "";
		$queryParam = array();
		$order = " ORDER BY u.id ASC";
		// select innactives
		$where .= " WHERE u.status = " . User::$STATUS_INACTIVE;
		// select not anonymized (not -yet- anonymized)
		$where .= " AND u.anonymized = :anonymized ";
		$queryParam['anonymized'] = false;
		//  select no active linked project (owner)
		$where .= " AND (SELECT COUNT (po.id) FROM Project po WHERE u.id = po.owner AND NOT (po.status = :po_status_arch OR po.status = :po_status_rejc)) = 0 ";
		$queryParam['po_status_arch'] = Project::$AD_STATUS_ARCHIVED;
		$queryParam['po_status_rejc'] = Project::$AD_STATUS_REJECTED;
		// select no active linked project (manager)
		$where .= " AND (SELECT COUNT (pm.id) FROM Project pm WHERE u.id = pm.analystInCharge AND NOT (pm.status = :pm_status_arch OR pm.status = :pm_status_rejc)) = 0 ";
		$queryParam['pm_status_arch'] = Project::$AD_STATUS_ARCHIVED;
		$queryParam['pm_status_rejc'] = Project::$AD_STATUS_REJECTED;
		// // CAN NOT SELECT "no active linked project" AS INVOLVED → select all 
		// $join .= " JOIN u.projectsInvolded upi "; // upi.id = pi.id 
		// $where .= " AND (SELECT COUNT (pi.id) FROM Project pi WHERE u.id = pi.analystsInvolved AND NOT (pi.status = :pi_status_arch OR pi.status = :pi_status_rejc)) = 0 ";
		// $queryParam['pi_status_arch'] = Project::$AD_STATUS_ARCHIVED;
		// $queryParam['pi_status_rejc'] = Project::$AD_STATUS_REJECTED;
		// select lastActivity > 5 years
		$lastAct = $trigger == Trigger::Warn ? GdprManagementService::$TRIGGER_WARN : GdprManagementService::$TRIGGER_ACTION;
		$old = (new \DateTime($lastAct));
		$where .= " AND u.lastActivity < :last_activity ";
		$queryParam['last_activity'] = $old->format('Y-m-d H:i:s');
		// run query
		$query = $entityManager->createQuery('SELECT u FROM User u ' . $join . $where . $order);
		$query->setParameters($queryParam);
		$users = $query->getResult();
		// return results
		return $users;
	}

	/**
	 * Test if a user personnal data can be anonymized
	 *
	 * @return <code>_YES</code> if its personnal data can be anonymized, <code>_NO__XXX</code> otherwise (where "XXX" describe the reason why the user can't be anonymized)
	 */
	public static function canUserBeAnonymized($userId)
	{
		// init
		$entityManager = $GLOBALS['entityManager'];
		// get user from DB 
		$user = $entityManager->getRepository('User')->find($userId);
		// test not null
		if ($user != null) {
			// test not anonymized
			if ($user->isAnonymized()) {
				return "_NO__user_already_anonymized";
			}
			// test no related project - owner
			$owner_total = ProjectManagementService::countProjects($user->getId(), null, "owner");
			$owner_rejec = ProjectManagementService::countProjects($user->getId(), "rejected", "owner");
			$owner_archi = ProjectManagementService::countProjects($user->getId(), "archived", "owner");
			if ($owner_total != ($owner_rejec + $owner_archi)) {
				return "_NO__user_is_owner_of_active_projects";
			}
			// test no related project - manager
			$inCharge_total = ProjectManagementService::countProjects($user->getId(), null, "inCharge");
			$inCharge_rejec = ProjectManagementService::countProjects($user->getId(), "rejected", "inCharge");
			$inCharge_archi = ProjectManagementService::countProjects($user->getId(), "archived", "inCharge");
			if ($inCharge_total != ($inCharge_rejec + $inCharge_archi)) {
				return "_NO__user_is_inCharge_of_active_projects";
			}
			// test no related project - involved
			$involved_total = ProjectManagementService::countProjects($user->getId(), null, "involved");
			$involved_rejec = ProjectManagementService::countProjects($user->getId(), "rejected", "involved");
			$involved_archi = ProjectManagementService::countProjects($user->getId(), "archived", "involved");
			if ($involved_total != ($involved_rejec + $involved_archi)) {
				return "_NO__user_is_involved_in_active_projects";
			}
			// all green!
			return "_YES";
		}
		return "_NO__user_not_found";
	}

	/**
	 * anonymiz a user personal data
	 *
	 * @return <code>true</code> if success, <code>false</code> otherwise
	 */
	public static function anonymizeUser($userId)
	{
		if (GdprManagementService::canUserBeAnonymized($userId) == '_YES') {
			// init
			$entityManager = $GLOBALS['entityManager'];
			// get
			$user = $entityManager->getRepository('User')->find($userId);
			// clean action
			$user->anonymize();
			// update
			$user->setUpdated();
			$entityManager->persist($user);
			$entityManager->flush();
			return true;
		}
		return false;
	}

}