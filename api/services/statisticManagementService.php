<?php

use Doctrine\ORM\Query\ResultSetMapping;
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
class StatisticManagementService
{

	/**
	 * Extra projects statistics with (very) advanced filters
	 *
	 * @return unknown
	 */
	public static function getProjectsStats()
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// check filters
		$filter = false;

		$where = "";
		$join = "";
		$group_concat = "";
		$group_by = "";

		// => step 0 fitler date (init)
		$filterDate = "";
		// date from
		$filterDateFrom = null;
		if (isset($_GET['from']) && $_GET['from'] != "" && $_GET['from'] != "undefined") {
			$filter = true;
			$filterDateFrom = $_GET['from'];
			$filterDate .= " (p.created >= :dateFrom ) ";
		}
		// date to
		$filterDateTo = null;
		if (isset($_GET['to']) && $_GET['to'] != "" && $_GET['to'] != "undefined") {
			$filter = true;
			if ($filterDate != "") {
				$filterDate .= " AND ";
			}
			$filterDateTo = $_GET['to'];
			$filterDate .= " (p.created <= :dateTo ) ";
		}
		// filter date: compilation
		if ($filterDate != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " (" . $filterDate . ") ";
		}

		// => step 1: filter PJ status
		$filterStatus = "";
		if (isset($_GET['isStatus'])) {
			foreach (preg_split('/,/', $_GET['isStatus']) as $k => $v) {
				switch (strtolower($v)) {
					case "rejected":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status = " . Project::$AD_STATUS_REJECTED . " ";
						break;
					case "waiting":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status = " . Project::$AD_STATUS_WAITING . " ";
						break;
					case "assigned":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status = " . Project::$AD_STATUS_ASSIGNED . " ";
						break;
					case "accepted":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status = " . Project::$AD_STATUS_ACCEPTED . " ";
						break;
					case "completed":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status = " . Project::$AD_STATUS_COMPLETED . " ";
						break;
					case "running":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status = " . Project::$AD_STATUS_RUNNING . " ";
						break;
					case "blocked":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status = " . Project::$AD_STATUS_BLOCKED . " ";
						break;
					case "archived":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status = " . Project::$AD_STATUS_ARCHIVED . " ";
						break;
					default:
						// echo $v;
						break;
				}
			}
		}
		if (isset($_GET['isNotStatus'])) {
			foreach (preg_split('/,/', $_GET['isNotStatus']) as $k => $v) {
				switch (strtolower($v)) {
					case "rejected":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status <> " . Project::$AD_STATUS_REJECTED . " ";
						break;
					case "waiting":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status <> " . Project::$AD_STATUS_WAITING . " ";
						break;
					case "assigned":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status <> " . Project::$AD_STATUS_ASSIGNED . " ";
						break;
					case "accepted":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status <> " . Project::$AD_STATUS_ACCEPTED . " ";
						break;
					case "completed":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status <> " . Project::$AD_STATUS_COMPLETED . " ";
						break;
					case "running":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status <> " . Project::$AD_STATUS_RUNNING . " ";
						break;
					case "blocked":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status <> " . Project::$AD_STATUS_BLOCKED . " ";
						break;
					case "archived":
						if ($filterStatus != "") {
							$filterStatus .= " OR ";
						}
						$filterStatus .= " p.project_status <> " . Project::$AD_STATUS_ARCHIVED . " ";
						break;
					default:
						// echo $v;
						break;
				}
			}
		}
		if ($filterStatus != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterStatus . " ) ";
		}

		// => step 2: filter PJ MTH PF
		$filterPlatForm = "";
		$countJointTable = 0;
		if (isset($_GET['isPlatForm'])) {
			foreach (preg_split('/,/', $_GET['isPlatForm']) as $k => $v) {
				// query with PF id
				if (intval($v) . "" == $v) {
					$join .= " LEFT JOIN projects_to_metabohub_platforms pj2mthpf" . $countJointTable . " ON p.id = pj2mthpf" . $countJointTable . ".project_id ";
					if ($filterPlatForm != "") {
						$filterPlatForm .= " AND ";
					}
					$filterPlatForm .= " pj2mthpf" . $countJointTable . ".metabohub_platform_id = " . intval($v) . " ";
					$countJointTable++;
				}
				// query with PF name
				// else {
				// }
			}
		}
		if (isset($_GET['isNotPlatForm'])) {
			foreach (preg_split('/,/', $_GET['isNotPlatForm']) as $k => $v) {
				// query with PF id
				if (intval($v) . "" == $v) {
					$join .= " LEFT JOIN projects_to_metabohub_platforms pj2mthpf" . $countJointTable . " ON p.id = pj2mthpf" . $countJointTable . ".project_id ";
					if ($filterPlatForm != "") {
						$filterPlatForm .= " AND ";
					}
					$filterPlatForm .= " pj2mthpf" . $countJointTable . ".metabohub_platform_id <> " . intval($v) . " ";
					$countJointTable++;
				}
				// query with PF name
				// else {
				// }
			}
		}
		if ($filterPlatForm != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterPlatForm . " ) ";
		}

		// => step 3: filter PJ thematic cloud word
		$filterKeyword = "";
		if (isset($_GET['isKeyword'])) {
			foreach (preg_split('/,/', $_GET['isKeyword']) as $k => $v) {
				// query with KW id
				if (intval($v) . "" == $v) {
					$join .= " LEFT JOIN projects_to_thematic_words pj2thkw" . $countJointTable . " ON p.id = pj2thkw" . $countJointTable . ".project_id ";
					if ($filterKeyword != "") {
						$filterKeyword .= " AND ";
					}
					$filterKeyword .= " pj2thkw" . $countJointTable . ".thematic_word_id = " . intval($v) . " ";
					$countJointTable++;
				}
				// query with PF name
				// else {
				// }
			}
		}
		if (isset($_GET['isNotKeyword'])) {
			foreach (preg_split('/,/', $_GET['isNotKeyword']) as $k => $v) {
				// query with KW id
				if (intval($v) . "" == $v) {
					$join .= " LEFT JOIN projects_to_thematic_words pj2thkw" . $countJointTable . " ON p.id = pj2thkw" . $countJointTable . ".project_id ";
					if ($filterKeyword != "") {
						$filterKeyword .= " AND ";
					}
					$filterKeyword .= " pj2thkw" . $countJointTable . ".thematic_word_id <> " . intval($v) . " ";
					$countJointTable++;
				}
				// query with KW name
				// else {
				// }
			}
		}
		if ($filterKeyword != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterKeyword . " ) ";
		}

		// => step 4: filter PJ type
		$filterType = "";
		if (isset($_GET['isType'])) {
			foreach (preg_split('/,/', $_GET['isType']) as $k => $v) {
				// query with filter type
				switch (strtolower($v)) {
					case "eq_prov":
					case "demand_type_eq_provisioning":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_eq_provisioning = 1 ";
						break;
					case "cat_allo":
					case "demand_type_catalog_allowance":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_catalog_allowance = 1 ";
						break;
					case "demand_type_feasibility_study":
					case "fea_stu":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_feasibility_study = 1 ";
						break;
					case "train":
					case "demand_type_training":
					case "demand_type_formation":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_training = 1 ";
						break;
					case "demand_type_data_processing":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_data_processing = 1 ";
						break;
					case "demand_type_other":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_other = 1 ";
						break;
					default:
						break;
				}
			}
		}
		if (isset($_GET['isNotType'])) {
			foreach (preg_split('/,/', $_GET['isNotType']) as $k => $v) {
				// query with filter type
				switch (strtolower($v)) {
					case "eq_prov":
					case "demand_type_eq_provisioning":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_eq_provisioning <> 1 ";
						break;
					case "cat_allo":
					case "demand_type_catalog_allowance":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_catalog_allowance <> 1 ";
						break;
					case "demand_type_feasibility_study":
					case "fea_stu":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_feasibility_study <> 1 ";
						break;
					case "train":
					case "demand_type_training":
					case "demand_type_formation":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_training <> 1 ";
						break;
					case "demand_type_data_processing":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_data_processing <> 1 ";
						break;
					case "demand_type_other":
						if ($filterType != "") {
							$filterType .= " OR ";
						}
						$filterType .= " p.demand_type_other <> 1 ";
						break;
					default:
						break;
				}
			}
		}
		if ($filterType != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterType . " ) ";
		}

		// => step 5: filter PJ financed type
		$filterFinanced = "";
		if (isset($_GET['isFinanced'])) {
			foreach (preg_split('/,/', $_GET['isFinanced']) as $k => $v) {
				// query with filter type
				switch (strtolower($v)) {
					case "financed":
					case "financial_context_is_project_financed":
						if ($filterFinanced != "") {
							$filterFinanced .= " OR ";
						}
						$filterFinanced .= " p.financial_context_is_project_financed = 1 ";
						break;
					case "provisioning":
					case "financial_context_is_project_in_provisioning":
						if ($filterFinanced != "") {
							$filterFinanced .= " OR ";
						}
						$filterFinanced .= " p.financial_context_is_project_in_provisioning = 1 ";
						break;
					case "financial_context_is_project_on_own_supply":
					case "own_supply":
						if ($filterFinanced != "") {
							$filterFinanced .= " OR ";
						}
						$filterFinanced .= " p.financial_context_is_project_on_own_supply = 1 ";
						break;
					case "not":
					case "not_financed":
					case "financial_context_is_project_not_financed":
						if ($filterFinanced != "") {
							$filterFinanced .= " OR ";
						}
						$filterFinanced .= " p.financial_context_is_project_not_financed = 1 ";
						break;
					default:
						break;
				}
			}
		}
		if (isset($_GET['isNotFinanced'])) {
			foreach (preg_split('/,/', $_GET['isNotFinanced']) as $k => $v) {
				// query with filter type
				switch (strtolower($v)) {
					case "financed":
					case "financial_context_is_project_financed":
						if ($filterFinanced != "") {
							$filterFinanced .= " OR ";
						}
						$filterFinanced .= " p.financial_context_is_project_financed <> 1 ";
						break;
					case "provisioning":
					case "financial_context_is_project_in_provisioning":
						if ($filterFinanced != "") {
							$filterFinanced .= " OR ";
						}
						$filterFinanced .= " p.financial_context_is_project_in_provisioning <> 1 ";
						break;
					case "financial_context_is_project_on_own_supply":
					case "own_supply":
						if ($filterFinanced != "") {
							$filterFinanced .= " OR ";
						}
						$filterFinanced .= " p.financial_context_is_project_on_own_supply <> 1 ";
						break;
					case "not":
					case "not_financed":
					case "financial_context_is_project_not_financed":
						if ($filterFinanced != "") {
							$filterFinanced .= " OR ";
						}
						$filterFinanced .= " p.financial_context_is_project_not_financed <> 1 ";
						break;
					default:
						break;
				}
			}
		}
		if ($filterFinanced != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterFinanced . " ) ";
		}

		// => step 6: filter PJ owner status / ...
		$filterOwner = "";
		if (isset($_GET['isOwner'])) {
			foreach (preg_split('/,/', $_GET['isOwner']) as $k => $v) {
				$arrayRet = self::userMultiFilter($v, $filterOwner, $join, $countJointTable, " = ");
				$filterOwner = $arrayRet[0];
				$join = $arrayRet[1];
				$countJointTable = $arrayRet[2];
			}
		}
		if (isset($_GET['isNotOwner'])) {
			foreach (preg_split('/,/', $_GET['isNotOwner']) as $k => $v) {
				$arrayRet = self::userMultiFilter($v, $filterOwner, $join, $countJointTable, " <> ");
				$filterOwner = $arrayRet[0];
				$join = $arrayRet[1];
				$countJointTable = $arrayRet[2];
				// query with KW id
				// if (intval ( $v ) . "" == $v) {
				// $join .= " LEFT JOIN projects_to_thematic_words pj2thkw" . $countJointTable . " ON p.id = pj2thkw" . $countJointTable . ".project_id ";
				// if ($filterOwner != "") {
				// $filterOwner .= " AND ";
				// }
				// $filterOwner .= " pj2thkw" . $countJointTable . ".thematic_word_id <> " . intval ( $v ) . " ";
				// $countJointTable ++;
				// }
				// query with KW name
				// else {
				// }
			}
		}
		if ($filterOwner != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterOwner . " ) ";
		}

		// => group contact
		if (isset($_GET['group'])) {
			if ($_GET['group'] == "status") {
				// $group_concat .= ", GROUP_CONCAT(DISTINCT p.project_status) AS projects_status ";
				// $group_by .= " GROUP BY (p.project_status) ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN p.project_status = '" . Project::$AD_STATUS_REJECTED . "'  THEN p.id END)) AS rejected ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN p.project_status = '" . Project::$AD_STATUS_WAITING . "'   THEN p.id END)) AS waiting ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN p.project_status = '" . Project::$AD_STATUS_ASSIGNED . "'  THEN p.id END)) AS assigned ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN p.project_status = '" . Project::$AD_STATUS_COMPLETED . "' THEN p.id END)) AS completed ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN p.project_status = '" . Project::$AD_STATUS_ACCEPTED . "'  THEN p.id END)) AS accepted ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN p.project_status = '" . Project::$AD_STATUS_RUNNING . "'   THEN p.id END)) AS running ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN p.project_status = '" . Project::$AD_STATUS_BLOCKED . "'   THEN p.id END)) AS blocked ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN p.project_status = '" . Project::$AD_STATUS_ARCHIVED . "'  THEN p.id END)) AS archived ";
				$group_by .= "  ";
			} else if ($_GET['group'] == "sample_number") {
				// $group_concat .= ", GROUP_CONCAT(DISTINCT p.samples_number) AS sample_number ";
				$group_concat .= " , COUNT(CASE WHEN p.samples_number   =  '" . Project::$AD_SAMPLE_NUMBER__LESS_THAN_50 . "'  THEN 1 END) AS less_50 ";
				$group_concat .= " , COUNT(CASE WHEN p.samples_number   =  '" . Project::$AD_SAMPLE_NUMBER__51_TO_100 . "'     THEN 1 END) AS 51_to_100 ";
				$group_concat .= " , COUNT(CASE WHEN p.samples_number   =  '" . Project::$AD_SAMPLE_NUMBER__101_TO_500 . "'    THEN 1 END) AS 101_to_500 ";
				$group_concat .= " , COUNT(CASE WHEN p.samples_number   =  '" . Project::$AD_SAMPLE_NUMBER__MORE_THAN_501 . "' THEN 1 END) AS more_501 ";
				$group_concat .= " , COUNT(CASE WHEN p.samples_number   =  '0'                                                 THEN 1 END) AS undef ";
				if ($where == "") {
					$where = " WHERE ";
				} else {
					$where .= " AND ";
				}
				$where .= " ( p.demand_type_catalog_allowance = '1' ) ";
				// $group_by .= " GROUP BY (p.samples_number) ";
				$group_by .= " ";
			} else if ($_GET['group'] == "mthPF") {
				$group_concat .= " , GROUP_CONCAT(DISTINCT(pj2pf.metabohub_platform_id)) pf_ids ";
				$group_concat .= " , GROUP_CONCAT(DISTINCT(pf.platform_name)) pf_names ";
				$join .= " LEFT JOIN projects_to_metabohub_platforms pj2pf ";
				$join .= " ON p.id = pj2pf.project_id ";
				$join .= " LEFT JOIN metabohub_platform pf ";
				$join .= " ON pj2pf.metabohub_platform_id = pf.id";
				$group_by .= " GROUP BY pf.id ";
			} else if ($_GET['group'] == "keywords") {
				$group_concat .= " , GROUP_CONCAT(DISTINCT(pj2kw.thematic_word_id)) tw_ids ";
				$group_concat .= " , GROUP_CONCAT(DISTINCT(tw.word)) tw_words ";
				$join .= " LEFT JOIN projects_to_thematic_words pj2kw ";
				$join .= " ON p.id = pj2kw.project_id ";
				$join .= " LEFT JOIN thematic_words tw ";
				$join .= " ON pj2kw.thematic_word_id = tw.id";
				$group_by .= " GROUP BY tw.id ";
			} else if ($_GET['group'] == "subkeywords") {
				$group_concat .= " , GROUP_CONCAT(DISTINCT(pj2kw.sub_thematic_word_id)) stw_ids ";
				$group_concat .= " , GROUP_CONCAT(DISTINCT(tw.word)) tw_words ";
				$join .= " LEFT JOIN projects_to_sub_thematic_words pj2kw ";
				$join .= " ON p.id = pj2kw.project_id ";
				$join .= " LEFT JOIN sub_thematic_words tw ";
				$join .= " ON pj2kw.sub_thematic_word_id = tw.id";
				$group_by .= " GROUP BY tw.id ";
			} else if ($_GET['group'] == "manager-keywords") { // mama#66
				$group_concat .= " , GROUP_CONCAT(DISTINCT(pj2kw.projects_managers_thematic_word_id)) mtw_ids ";
				$group_concat .= " , GROUP_CONCAT(DISTINCT(tw.word)) tw_words ";
				// $join .= " LEFT JOIN projects_extra_datum pje ";
				// $join .= " ON p.id = pje.project_id ";
				$join .= " LEFT JOIN projects_to_managers_thematic_words pj2kw ";
				$join .= " ON p.id = pj2kw.project_id ";//project_ext_data_id
				$join .= " LEFT JOIN manager_thematic_words tw ";
				$join .= " ON pj2kw.projects_managers_thematic_word_id = tw.id";
				$group_by .= " GROUP BY tw.id ";
			} else if ($_GET['group'] == "mth-sub-platforms") { // mama#65
				$group_concat .= " , GROUP_CONCAT(DISTINCT(pj2spf.metabohub_sub_platform_id)) sub_pf_ids ";
				$group_concat .= " , GROUP_CONCAT(DISTINCT(sub_pf.sub_platform_name)) sub_pf_names ";
				$join .= " LEFT JOIN projects_ext_data_to_metabohub_sub_platforms pj2spf ";
				$join .= " ON p.id = pj2spf.project_id ";//project_ext_data_id
				$join .= " LEFT JOIN metabohub_sub_platform sub_pf ";
				$join .= " ON pj2spf.metabohub_sub_platform_id = sub_pf.id";
				$group_by .= " GROUP BY sub_pf.id ";
			} else if ($_GET['group'] == "type") {
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_eq_provisioning   =  '1' THEN 1 END) AS dt__eqprov ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_eq_provisioning   <> '1' THEN 1 END) AS dt__NOT_eqprov ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_catalog_allowance =  '1' THEN 1 END) AS dt__catallo ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_catalog_allowance <> '1' THEN 1 END) AS dt__NOT_catallo ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_feasibility_study =  '1' THEN 1 END) AS dt__feastu ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_feasibility_study <> '1' THEN 1 END) AS dt__NOT_feastu ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_training          =  '1' THEN 1 END) AS dt__train ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_training          <> '1' THEN 1 END) AS dt__NOT_train ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_data_processing   =  '1' THEN 1 END) AS dt__data_proc ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_data_processing   <> '1' THEN 1 END) AS dt__NOT_data_proc ";
				$group_concat .= " , COUNT(CASE WHEN p.demand_type_other             =  '1' THEN 1 END) AS dt__other ";
				$group_concat .= " , COUNT(CASE WHEN p. demand_type_other            <> '1' THEN 1 END) AS dt__NOT_other ";
				$group_by .= " ";
			} else if ($_GET['group'] == "targeted") {
				$group_concat .= " , COUNT(CASE WHEN p.targeted = '1'   THEN 1 END) AS is_targeted ";
				$group_concat .= " , COUNT(CASE WHEN p.targeted = '0'   THEN 1 END) AS is_NOT_targeted ";
				$group_concat .= " , COUNT(CASE WHEN p.targeted IS NULL THEN 1 END) AS undef ";
				if ($where == "") {
					$where = " WHERE ";
				} else {
					$where .= " AND ";
				}
				$where .= " ( p.demand_type_catalog_allowance = '1' ) ";
				$group_by .= " ";
			} else if ($_GET['group'] == "copartner") {
				$group_concat .= " , COUNT(CASE WHEN p.can_be_forwarded_to_copartner =  '1'  THEN 1 END) AS can_be_fwd ";
				$group_concat .= " , COUNT(CASE WHEN p.can_be_forwarded_to_copartner =  '0'  THEN 1 END) AS can_not_be_fwd ";
				$group_concat .= " , COUNT(CASE WHEN p.can_be_forwarded_to_copartner IS NULL THEN 1 END) AS undef ";
				$group_by .= " ";
			} else if ($_GET['group'] == "financial") {
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_financed        =  '1' THEN 1 END) AS f__financed ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_financed        <> '1' THEN 1 END) AS f__NOT_financed ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_in_provisioning =  '1' THEN 1 END) AS f__provisioning ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_in_provisioning <> '1' THEN 1 END) AS f__NOT_provisioning ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_on_own_supply   =  '1' THEN 1 END) AS f__ownsupply ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_on_own_supply   <> '1' THEN 1 END) AS f__NOT_ownsupply ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_not_financed    =  '1' THEN 1 END) AS f__notfinanced ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_not_financed    <> '1' THEN 1 END) AS f__NOT_notfinanced ";
				$group_by .= " ";
			}
			// mama#46
			else if ($_GET['group'] == "financial_type") {
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_eu          =  '1' THEN 1 END) AS fs__eu ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_eu          <> '1' THEN 1 END) AS fs__NOT_eu ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_anr         =  '1' THEN 1 END) AS fs__anr ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_anr         <> '1' THEN 1 END) AS fs__NOT_anr ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_national    =  '1' THEN 1 END) AS fs__national ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_national    <> '1' THEN 1 END) AS fs__NOT_national ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_regional    =  '1' THEN 1 END) AS fs__regional ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_regional    <> '1' THEN 1 END) AS fs__NOT_regional ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_compagny_tutorship          =  '1' THEN 1 END) AS fs__compagny_tutorship ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_compagny_tutorship          <> '1' THEN 1 END) AS fs__NOT_compagny_tutorship ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_international_outside_eu    =  '1' THEN 1 END) AS fs__international_outside_eu ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_international_outside_eu    <> '1' THEN 1 END) AS fs__NOT_international_outside_eu ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_own_resources_laboratory    =  '1' THEN 1 END) AS fs__own_resources_laboratory ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_own_resources_laboratory    <> '1' THEN 1 END) AS fs__NOT_own_resources_laboratory ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_other                       =  '1' THEN 1 END) AS fs__other ";
				$group_concat .= " , COUNT(CASE WHEN p.financial_context_is_project_other                       <> '1' THEN 1 END) AS fs__NOT_other ";
				$group_by .= " ";
			}
		}

		// init sql query
		$sql = 'SELECT COUNT(DISTINCT(p.id)) AS projects_count ' . $group_concat . ' FROM projects p ' . $join . $where . $group_by;
		// echo $sql;
		// init query params
		$queryParam = array();
		if (!is_null($filterDateFrom)) {
			// $filterDateFrom = date_parse_from_format ( "yyyy-mm-dd", $filterDateFrom );
			$date = date_create($filterDateFrom);
			$filterDateFrom = date_format($date, "Y-m-d");
			$queryParam['dateFrom'] = "" . $filterDateFrom . "";
		}
		if (!is_null($filterDateTo)) {
			// $filterDateTo = date_parse_from_format ( "yyyy-mm-dd", $filterDateTo );
			$date = date_create($filterDateTo);
			$filterDateTo = date_format($date, "Y-m-d");
			$queryParam['dateTo'] = "" . $filterDateTo . " 23:59:59";
		}
		// init result form
		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('projects_count', 'projects_count');
		$rsm->addScalarResult('projects_status', 'projects_status');

		$rsm->addScalarResult('rejected', 'rejected');
		$rsm->addScalarResult('waiting', 'waiting');
		$rsm->addScalarResult('assigned', 'assigned');
		$rsm->addScalarResult('completed', 'completed');
		$rsm->addScalarResult('accepted', 'accepted');
		$rsm->addScalarResult('running', 'running');
		$rsm->addScalarResult('blocked', 'blocked');
		$rsm->addScalarResult('archived', 'archived');

		$rsm->addScalarResult('sample_number', 'sample_number');
		$rsm->addScalarResult('pf_ids', 'pf_ids');
		$rsm->addScalarResult('pf_names', 'pf_names');
		$rsm->addScalarResult('tw_ids', 'tw_ids');
		$rsm->addScalarResult('stw_ids', 'stw_ids');
		$rsm->addScalarResult('mtw_ids', 'mtw_ids'); // mama#66
		$rsm->addScalarResult('tw_words', 'tw_words');
		$rsm->addScalarResult('sub_pf_ids', 'sub_pf_ids'); // mama#65
		$rsm->addScalarResult('sub_pf_names', 'sub_pf_names'); // mama#65
		$rsm->addScalarResult('dt__eqprov', 'dt__eqprov');
		$rsm->addScalarResult('dt__NOT_eqprov', 'dt__NOT_eqprov');
		$rsm->addScalarResult('dt__catallo', 'dt__catallo');
		$rsm->addScalarResult('dt__NOT_catallo', 'dt__NOT_catallo');
		$rsm->addScalarResult('dt__feastu', 'dt__feastu');
		$rsm->addScalarResult('dt__NOT_feastu', 'dt__NOT_feastu');
		$rsm->addScalarResult('dt__train', 'dt__train');
		$rsm->addScalarResult('dt__NOT_train', 'dt__NOT_train');
		$rsm->addScalarResult('dt__data_proc', 'dt__data_proc');
		$rsm->addScalarResult('dt__NOT_data_proc', 'dt__NOT_data_proc');
		$rsm->addScalarResult('dt__other', 'dt__other');
		$rsm->addScalarResult('dt__NOT_other', 'dt__NOT_other');

		$rsm->addScalarResult('is_targeted', 'is_targeted');
		$rsm->addScalarResult('is_NOT_targeted', 'is_NOT_targeted');

		$rsm->addScalarResult('can_be_fwd', 'can_be_fwd');
		$rsm->addScalarResult('can_not_be_fwd', 'can_not_be_fwd');

		$rsm->addScalarResult('f__financed', 'f__financed');
		$rsm->addScalarResult('f__NOT_financed', 'f__NOT_financed');
		$rsm->addScalarResult('f__provisioning', 'f__provisioning');
		$rsm->addScalarResult('f__NOT_provisioning', 'f__NOT_provisioning');
		$rsm->addScalarResult('f__ownsupply', 'f__ownsupply');
		$rsm->addScalarResult('f__NOT_ownsupply', 'f__NOT_ownsupply');
		$rsm->addScalarResult('f__notfinanced', 'f__notfinanced');
		$rsm->addScalarResult('f__NOT_notfinanced', 'f__NOT_notfinanced');

		// mama#47 - new indicators
		$rsm->addScalarResult('fs__eu', 'fs__eu');
		$rsm->addScalarResult('fs__NOT_eu', 'fs__NOT_eu');
		$rsm->addScalarResult('fs__anr', 'fs__anr');
		$rsm->addScalarResult('fs__NOT_anr', 'fs__NOT_anr');
		$rsm->addScalarResult('fs__national', 'fs__national');
		$rsm->addScalarResult('fs__NOT_national', 'fs__NOT_national');
		$rsm->addScalarResult('fs__regional', 'fs__regional');
		$rsm->addScalarResult('fs__NOT_regional', 'fs__NOT_regional');
		$rsm->addScalarResult('fs__compagny_tutorship', 'fs__compagny_tutorship');
		$rsm->addScalarResult('fs__NOT_compagny_tutorship', 'fs__NOT_compagny_tutorship');
		$rsm->addScalarResult('fs__international_outside_eu', 'fs__international_outside_eu');
		$rsm->addScalarResult('fs__NOT_international_outside_eu', 'fs__NOT_international_outside_eu');
		$rsm->addScalarResult('fs__own_resources_laboratory', 'fs__own_resources_laboratory');
		$rsm->addScalarResult('fs__NOT_own_resources_laboratory', 'fs__NOT_own_resources_laboratory');
		$rsm->addScalarResult('fs__other', 'fs__other');
		$rsm->addScalarResult('fs__NOT_other', 'fs__NOT_other');

		$rsm->addScalarResult('less_50', 'less_50');
		$rsm->addScalarResult('51_to_100', '51_to_100');
		$rsm->addScalarResult('101_to_500', '101_to_500');
		$rsm->addScalarResult('more_501', 'more_501');
		$rsm->addScalarResult('undef', 'undef');

		// query
		$query = $entityManager->createNativeQuery($sql, $rsm);
		$query->setParameters($queryParam);
		$projectsCount = $query->getResult();
		// var_dump ( $projectsCount );
		// exit ();
		return $projectsCount;
	}

	/**
	 * Extra users statistics with (very) advanced filters
	 * WARNING: date filter is on user's project's created date!!!
	 *
	 * @return unknown
	 */
	public static function getUsersStats()
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// check filters
		$filter = false;

		$where = "";
		$join = "";
		$group_concat = "";
		$group_by = "";

		// => step 0 fitler date (init)
		$filterDate = "";
		// date from
		$filterDateFrom = null;
		if (isset($_GET['from']) && $_GET['from'] != "" && $_GET['from'] != "undefined") {
			$filter = true;
			$filterDateFrom = $_GET['from'];
			$filterDate .= " (p.created >= :dateFrom ) ";
		}
		// date to
		$filterDateTo = null;
		if (isset($_GET['to']) && $_GET['to'] != "" && $_GET['to'] != "undefined") {
			$filter = true;
			if ($filterDate != "") {
				$filterDate .= " AND ";
			}
			$filterDateTo = $_GET['to'];
			$filterDate .= " (p.created <= :dateTo ) ";
		}
		// filter date: compilation
		if ($filterDate != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$join .= " LEFT JOIN projects p ON u.id = p.owner_id ";
			$where .= " (" . $filterDate . ") ";
		}

		// TODO list all filter
		// => step 1: filter user labo type
		$filterUserLaboType = "";
		if (isset($_GET['isUserLaboType'])) {
			foreach (preg_split('/,/', $_GET['isUserLaboType']) as $k => $v) {
				// query with KW id
				switch (strtolower($v)) {
					case "public":
						if ($filterUserLaboType != "") {
							$filterUserLaboType .= " AND ";
						}
						$filterUserLaboType .= " u.laboratory_type = '" . User::$LABO_TYPE_PUBLIC . "' ";
						break;
					case "private":
						if ($filterUserLaboType != "") {
							$filterUserLaboType .= " AND ";
						}
						$filterUserLaboType .= " u.laboratory_type = '" . User::$LABO_TYPE_PRIVATE . "' ";
						break;
					case "public_private":
						if ($filterUserLaboType != "") {
							$filterUserLaboType .= " AND ";
						}
						$filterUserLaboType .= " u.laboratory_type = '" . User::$LABO_TYPE_PUBLIC_PRIVATE . "' ";
						break;
				}
			}
		}
		if (isset($_GET['isNotUserLaboType'])) {
			foreach (preg_split('/,/', $_GET['isNotUserLaboType']) as $k => $v) {
				switch (strtolower($v)) {
					case "public":
						if ($filterUserLaboType != "") {
							$filterUserLaboType .= " AND ";
						}
						$filterUserLaboType .= " u.laboratory_type <> '" . User::$LABO_TYPE_PUBLIC . "' ";
						break;
					case "private":
						if ($filterUserLaboType != "") {
							$filterUserLaboType .= " AND ";
						}
						$filterUserLaboType .= " u.laboratory_type <> '" . User::$LABO_TYPE_PRIVATE . "' ";
						break;
					case "public_private":
						if ($filterUserLaboType != "") {
							$filterUserLaboType .= " AND ";
						}
						$filterUserLaboType .= " u.laboratory_type <> '" . User::$LABO_TYPE_PUBLIC_PRIVATE . "' ";
						break;
				}
			}
		}
		if ($filterUserLaboType != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterUserLaboType . " ) ";
		}

		// => step 2: filter PJ MTH PF
		$filterPlatForm = "";
		$countJointTable = 0;
		if (isset($_GET['isPlatForm'])) {
			foreach (preg_split('/,/', $_GET['isPlatForm']) as $k => $v) {
				// query with PF id
				if (intval($v) . "" == $v) {
					$join .= " LEFT JOIN projects_to_metabohub_platforms pj2mthpf" . $countJointTable . " ON p.id = pj2mthpf" . $countJointTable . ".project_id ";
					if ($filterPlatForm != "") {
						$filterPlatForm .= " AND ";
					}
					$filterPlatForm .= " pj2mthpf" . $countJointTable . ".metabohub_platform_id = " . intval($v) . " ";
					$countJointTable++;
				}
				// query with PF name
				// else {
				// }
			}
		}
		if (isset($_GET['isNotPlatForm'])) {
			foreach (preg_split('/,/', $_GET['isNotPlatForm']) as $k => $v) {
				// query with PF id
				if (intval($v) . "" == $v) {
					$join .= " LEFT JOIN projects_to_metabohub_platforms pj2mthpf" . $countJointTable . " ON p.id = pj2mthpf" . $countJointTable . ".project_id ";
					if ($filterPlatForm != "") {
						$filterPlatForm .= " AND ";
					}
					$filterPlatForm .= " pj2mthpf" . $countJointTable . ".metabohub_platform_id <> " . intval($v) . " ";
					$countJointTable++;
				}
				// query with PF name
				// else {
				// }
			}
		}
		if ($filterPlatForm != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterPlatForm . " ) ";
		}

		// TODO list all groups
		// => group contact
		if (isset($_GET['group'])) {
			if ($_GET['group'] == "laboratory") {
				// $group_concat .= ", GROUP_CONCAT(DISTINCT u.laboratory_type) AS projects_status ";
				// $group_by .= " GROUP BY (u.laboratory_type) ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN u.laboratory_type = '" . User::$LABO_TYPE_PUBLIC . "'         THEN u.id END)) AS u_labo_public ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN u.laboratory_type = '" . User::$LABO_TYPE_PRIVATE . "'        THEN u.id END)) AS u_labo_private ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN u.laboratory_type = '" . User::$LABO_TYPE_PUBLIC_PRIVATE . "' THEN u.id END)) AS u_labo_public_private ";
				$group_by .= "  ";
			}
		}

		// init sql query
		$sql = 'SELECT COUNT(DISTINCT(u.id)) AS users_count ' . $group_concat . ' FROM users u ' . $join . $where . $group_by;
		// echo $sql;
		// init query params
		$queryParam = array();
		if (!is_null($filterDateFrom)) {
			// $filterDateFrom = date_parse_from_format ( "yyyy-mm-dd", $filterDateFrom );
			$date = date_create($filterDateFrom);
			$filterDateFrom = date_format($date, "Y-m-d");
			$queryParam['dateFrom'] = "" . $filterDateFrom . "";
		}
		if (!is_null($filterDateTo)) {
			// $filterDateTo = date_parse_from_format ( "yyyy-mm-dd", $filterDateTo );
			$date = date_create($filterDateTo);
			$filterDateTo = date_format($date, "Y-m-d");
			$queryParam['dateTo'] = "" . $filterDateTo . " 23:59:59";
		}
		// init result form
		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('users_count', 'users_count');
		$rsm->addScalarResult('laboratory_type', 'laboratory_type');

		$rsm->addScalarResult('u_labo_public', 'u_labo_public');
		$rsm->addScalarResult('u_labo_private', 'u_labo_private');
		$rsm->addScalarResult('u_labo_public_private', 'u_labo_public_private');

		// query
		$query = $entityManager->createNativeQuery($sql, $rsm);
		$query->setParameters($queryParam);
		$usersCount = $query->getResult();
		// var_dump ( $projectsCount );
		// exit ();
		return $usersCount;
	}

	/**
	 * Extra users statistics with (very) advanced filters
	 * WARNING: date filter is on user's project's created date!!!
	 *
	 * @return unknown
	 */
	public static function getExtraDataStats()
	{

		// init
		$entityManager = $GLOBALS['entityManager'];

		// check filters
		$filter = false;

		$where = "";
		$join = "";
		$group_concat = "";
		$group_by = "";

		// => step 0 fitler date (init)
		$filterDate = "";
		// date from
		$filterDateFrom = null;
		if (isset($_GET['from']) && $_GET['from'] != "" && $_GET['from'] != "undefined") {
			$filter = true;
			$filterDateFrom = $_GET['from'];
			$filterDate .= " (p.created >= :dateFrom ) ";
		}
		// date to
		$filterDateTo = null;
		if (isset($_GET['to']) && $_GET['to'] != "" && $_GET['to'] != "undefined") {
			$filter = true;
			if ($filterDate != "") {
				$filterDate .= " AND ";
			}
			$filterDateTo = $_GET['to'];
			$filterDate .= " (p.created <= :dateTo ) ";
		}
		// filter date: compilation
		if ($filterDate != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$join .= " LEFT JOIN projects p ON e.project_id = p.id ";
			$where .= " (" . $filterDate . ") ";
		}

		// TODO list all filter

		// => step 1: filter projects labo type
		$filterProjectLaboType = "";
		if (isset($_GET['isProjectLaboType'])) {
			foreach (preg_split('/,/', $_GET['isProjectLaboType']) as $k => $v) {
				// query with KW id
				switch (strtolower($v)) {
					case "public":
						if ($filterProjectLaboType != "") {
							$filterProjectLaboType .= " OR ";
						}
						$filterProjectLaboType .= " e.laboratory_type = '" . ProjectExtraData::$LABO_TYPE_PUBLIC . "' ";
						break;
					case "private":
						if ($filterProjectLaboType != "") {
							$filterProjectLaboType .= " OR ";
						}
						$filterProjectLaboType .= " e.laboratory_type = '" . ProjectExtraData::$LABO_TYPE_PRIVATE . "' ";
						break;
					case "public_private":
						if ($filterProjectLaboType != "") {
							$filterProjectLaboType .= " OR ";
						}
						$filterProjectLaboType .= " e.laboratory_type = '" . ProjectExtraData::$LABO_TYPE_PRIVATE_PUBLIC . "' ";
						break;
				}
			}
		}
		if (isset($_GET['isNotProjectLaboType'])) {
			foreach (preg_split('/,/', $_GET['isNotProjectLaboType']) as $k => $v) {
				switch (strtolower($v)) {
					case "public":
						if ($filterProjectLaboType != "") {
							$filterProjectLaboType .= " AND ";
						}
						$filterProjectLaboType .= " e.laboratory_type <> '" . ProjectExtraData::$LABO_TYPE_PUBLIC . "' ";
						break;
					case "private":
						if ($filterProjectLaboType != "") {
							$filterProjectLaboType .= " AND ";
						}
						$filterProjectLaboType .= " e.laboratory_type <> '" . ProjectExtraData::$LABO_TYPE_PRIVATE . "' ";
						break;
					case "public_private":
						if ($filterProjectLaboType != "") {
							$filterProjectLaboType .= " AND ";
						}
						$filterProjectLaboType .= " e.laboratory_type <> '" . ProjectExtraData::$LABO_TYPE_PRIVATE_PUBLIC . "' ";
						break;
				}
			}
		}
		if ($filterProjectLaboType != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterProjectLaboType . " ) ";
		}

		// => step 2: filter PJ MTH PF
		$filterPlatForm = "";
		$countJointTable = 0;
		if (isset($_GET['isPlatForm'])) {
			foreach (preg_split('/,/', $_GET['isPlatForm']) as $k => $v) {
				// query with PF id
				if (intval($v) . "" == $v) {
					$join .= " LEFT JOIN projects_to_metabohub_platforms pj2mthpf" . $countJointTable . " ON p.id = pj2mthpf" . $countJointTable . ".project_id ";
					if ($filterPlatForm != "") {
						$filterPlatForm .= " AND ";
					}
					$filterPlatForm .= " pj2mthpf" . $countJointTable . ".metabohub_platform_id = " . intval($v) . " ";
					$countJointTable++;
				}
				// query with PF name
				// else {
				// }
			}
		}
		if (isset($_GET['isNotPlatForm'])) {
			foreach (preg_split('/,/', $_GET['isNotPlatForm']) as $k => $v) {
				// query with PF id
				if (intval($v) . "" == $v) {
					$join .= " LEFT JOIN projects_to_metabohub_platforms pj2mthpf" . $countJointTable . " ON p.id = pj2mthpf" . $countJointTable . ".project_id ";
					if ($filterPlatForm != "") {
						$filterPlatForm .= " AND ";
					}
					$filterPlatForm .= " pj2mthpf" . $countJointTable . ".metabohub_platform_id <> " . intval($v) . " ";
					$countJointTable++;
				}
				// query with PF name
				// else {
				// }
			}
		}
		if ($filterPlatForm != "") {
			if ($where == "") {
				$where = " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= " ( " . $filterPlatForm . " ) ";
		}

		// TODO list all groups
		// => group contact
		if (isset($_GET['group'])) {
			if ($_GET['group'] == "laboratory") {
				// $group_concat .= ", GROUP_CONCAT(DISTINCT u.laboratory_type) AS projects_status ";
				// $group_by .= " GROUP BY (u.laboratory_type) ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN e.laboratory_type = '" . ProjectExtraData::$LABO_TYPE_PUBLIC . "'         THEN e.id END)) AS e_labo_public ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN e.laboratory_type = '" . ProjectExtraData::$LABO_TYPE_PRIVATE . "'        THEN e.id END)) AS e_labo_private ";
				$group_concat .= " , COUNT(DISTINCT(CASE WHEN e.laboratory_type = '" . ProjectExtraData::$LABO_TYPE_PRIVATE_PUBLIC . "' THEN e.id END)) AS e_labo_public_private ";
				$group_by .= "  ";
			}
		}

		// init sql query
		$sql = 'SELECT COUNT(DISTINCT(e.id)) AS extra_count ' . $group_concat . ' FROM projects_extra_datum e ' . $join . $where . $group_by;
		// echo $sql;
		// init query params
		$queryParam = array();
		if (!is_null($filterDateFrom)) {
			// $filterDateFrom = date_parse_from_format ( "yyyy-mm-dd", $filterDateFrom );
			$date = date_create($filterDateFrom);
			$filterDateFrom = date_format($date, "Y-m-d");
			$queryParam['dateFrom'] = "" . $filterDateFrom . "";
		}
		if (!is_null($filterDateTo)) {
			// $filterDateTo = date_parse_from_format ( "yyyy-mm-dd", $filterDateTo );
			$date = date_create($filterDateTo);
			$filterDateTo = date_format($date, "Y-m-d");
			$queryParam['dateTo'] = "" . $filterDateTo . " 23:59:59";
		}
		// init result form
		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('extra_count', 'extra_count');
		$rsm->addScalarResult('laboratory_type', 'laboratory_type');

		$rsm->addScalarResult('e_labo_public', 'e_labo_public');
		$rsm->addScalarResult('e_labo_private', 'e_labo_private');
		$rsm->addScalarResult('e_labo_public_private', 'e_labo_public_private');

		// query
		$query = $entityManager->createNativeQuery($sql, $rsm);
		$query->setParameters($queryParam);
		$usersCount = $query->getResult();
		// var_dump ( $projectsCount );
		// exit ();
		return $usersCount;
	}

	// TODO same with EVENT to do stat about project timelaps

	/**
	 *
	 * @param unknown $v        	
	 * @param unknown $filterOwner        	
	 * @param unknown $join        	
	 * @param unknown $countJointTable        	
	 * @param unknown $linker        	
	 * @return unknown[]|string[]
	 */
	private static function userMultiFilter($v, $filterOwner, $join, $countJointTable, $linker)
	{
		// query with KW id
		switch (strtolower($v)) {
			case "public":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".laboratory_type " . $linker . " " . User::$LABO_TYPE_PUBLIC . " ";
				$countJointTable++;
				break;
			case "private":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".laboratory_type " . $linker . " " . User::$LABO_TYPE_PRIVATE . " ";
				$countJointTable++;
				break;
			case "public_private":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".laboratory_type " . $linker . " " . User::$LABO_TYPE_PUBLIC_PRIVATE . " ";
				$countJointTable++;
				break;
			case "active":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".user_status " . $linker . " " . User::$STATUS_ACTIVE . " ";
				$countJointTable++;
				break;
			case "not_validated":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".user_status " . $linker . " " . User::$STATUS_NOT_VALIDATED . " ";
				$countJointTable++;
				break;
			case "blocked":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".user_status " . $linker . " " . User::$STATUS_BLOCKED . " ";
				$countJointTable++;
				break;
			case "inactive":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".user_status " . $linker . " " . User::$STATUS_INACTIVE . " ";
				$countJointTable++;
				break;
			case "admin":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".user_right " . $linker . " " . User::$RIGHT_ADMIN . " ";
				$countJointTable++;
				break;
			case "user":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".user_right " . $linker . " " . User::$RIGHT_USER . " ";
				$countJointTable++;
				break;
			case "project_manager":
				$join .= " LEFT JOIN users u" . $countJointTable . " ON p.owner_id = u" . $countJointTable . ".id ";
				if ($filterOwner != "") {
					$filterOwner .= " AND ";
				}
				$filterOwner .= " u" . $countJointTable . ".user_right " . $linker . " " . User::$RIGHT_PROJECT_MANAGER . " ";
				$countJointTable++;
				break;
			default:
				break;
		}
		return array(
			$filterOwner,
			$join,
			$countJointTable
		);
		// query with PF name
		// else {
		// }
	}
}
