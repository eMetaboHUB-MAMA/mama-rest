<?php

/**
    * Include PHPExcel
    */
require_once dirname ( __FILE__ ) . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// //////////////////////////////////////////////////////////////////////////////////////////////
// FORMAT FUNCTIONS
$magicAlphabet;
$currentSheet;

$styleGeen = array (
		'fill' => array (
				'fillType' => Fill::FILL_SOLID,
				'color' => array (
						'rgb' => '00FF00' 
				) 
		) 
);
$styleRed = array (
		'fill' => array (
				'fillType' => Fill::FILL_SOLID,
				'color' => array (
						'rgb' => 'FF0000' 
				) 
		) 
);

/**
 *
 * @param unknown $type        	
 * @param unknown $data        	
 */
function getXLSstatisticsFile() {

	// Create new PHPExcel object
	$objPHPExcel = new Spreadsheet ();
	// Set document properties
	$objPHPExcel->getProperties ()->setCreator ( "MAMA - Bot (>*.*)>" );
	$objPHPExcel->getProperties ()->setLastModifiedBy ( "MAMA - Bot (>*.*)>" );
	$objPHPExcel->getProperties ()->setTitle ( "MAMA - statistics" );
	$objPHPExcel->getProperties ()->setSubject ( "MAMA - statistics" );
	$objPHPExcel->getProperties ()->setDescription ( "list of all statistics ref. in MAMA API" );
	$objPHPExcel->getProperties ()->setKeywords ( "MAMA statistics" );
	$objPHPExcel->getProperties ()->setCategory ( "XLS file" );
	
	// build projects stats sheet
	buildProjectsStatsSheet ( $objPHPExcel );
	
	// build projects stats sheet
	buildProjectsPFStatsSheet ( $objPHPExcel );
	
	// build users stats sheet
	buildUsersStatsSheet ( $objPHPExcel );
	
	// set active sheet
	$objPHPExcel->setActiveSheetIndex ( 0 );
	
	// Redirect output to a client’s web browser (Excel5)
	header ( 'Content-Type: application/vnd.ms-excel' );
	header ( 'Content-Disposition: attachment;filename="statistics.xls"' );
	header ( 'Cache-Control: max-age=0' );
	// If you're serving to IE 9, then the following may be needed
	header ( 'Cache-Control: max-age=1' );
	// If you're serving to IE over SSL, then the following may be needed
	header ( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); // Date in the past
	header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' ); // always modified
	header ( 'Cache-Control: cache, must-revalidate' ); // HTTP/1.1
	header ( 'Pragma: public' ); // HTTP/1.0
	
	$writer = new Xlsx($objPHPExcel);
	$writer->save('php://output');
	
	exit ();
}

/**
 *
 * @param
 *        	objPHPExcel
 */
function buildUsersStatsSheet($objPHPExcel) {
	//
	cleanRequestGET ();
	
	// //////////////////////////////////////
	// SHEET 2: user stats
	global $styleRed;
	global $styleGeen;
	global $magicAlphabet;
	global $currentSheet;
	
	$currentSheet = 2;
	$objPHPExcel->createSheet ( $currentSheet );
	
	// //////////////////////////////////////
	// header
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A1', 'from' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'B1', $_GET ['from'] );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'C1', 'to' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'D1', $_GET ['to'] );
	
	// //////////////////////////////////////
	// get all users (generic)
	$allUsers = StatisticManagementService::getUsersStats ();
	// var_dump($allUsers) ; exit;
	
	// //////////////////////////////////////
	// TABLE 1: users X labo type
	
	// TABLE 1: headers
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A3', 'actif users X labo type' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A4', 'x' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'B4', 'count' );
	// left border
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A5', 'public' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A6', 'private' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A7', 'public / private' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A8', 'total' );
	
	// TABLE 1: content
	// all
	$_GET ['group'] = "laboratory";
	
	$userLabo = StatisticManagementService::getUsersStats ();
	$userLabo = $userLabo [0];
	// var_dump ( $userLabo ); exit ();
	
	buildCountUserXLaboTypeLine ( $objPHPExcel, '5', $userLabo, 'u_labo_public' );
	buildCountUserXLaboTypeLine ( $objPHPExcel, '6', $userLabo, 'u_labo_private' );
	buildCountUserXLaboTypeLine ( $objPHPExcel, '7', $userLabo, 'u_labo_public_private' );
	// // total x total
	buildCountUserXLaboTypeLine ( $objPHPExcel, '8', $userLabo, 'users_count' );
	
	// formatting
	$objPHPExcel->setActiveSheetIndex ( $currentSheet );
	$objPHPExcel->getActiveSheet ()->getStyle ( 'A4:B4' )->getFont ()->setBold ( true );
	$objPHPExcel->getActiveSheet ()->getStyle ( 'A3:A8' )->getFont ()->setBold ( true );
	
	// //////////////////////////////////////
	
	// users X projects-status
	
	$objPHPExcel->setActiveSheetIndex ( $currentSheet );
	$objPHPExcel->getActiveSheet ()->setTitle ( "users" );
}

/**
 *
 * @param
 *        	objPHPExcel
 */
function buildProjectsStatsSheet($objPHPExcel) {
	//
	cleanRequestGET ();
	
	// //////////////////////////////////////
	// SHEET 1: project stats
	global $styleRed;
	global $styleGeen;
	global $magicAlphabet;
	global $currentSheet;
	$magicAlphabet = Array (
			0 => "A",
			1 => "B",
			2 => "C",
			3 => "D",
			4 => "E",
			5 => "F",
			6 => "G",
			7 => "H",
			8 => "I",
			9 => "J",
			10 => "K",
			11 => "L",
			12 => "M",
			13 => "N",
			14 => "O",
			15 => "P",
			16 => "Q",
			17 => "R",
			18 => "S",
			19 => "T",
			20 => "U",
			21 => "V",
			22 => "W",
			23 => "X",
			24 => "Y",
			25 => "Z" 
	);
	for($i = 0; $i <= 25; $i ++) {
		$magicAlphabet [(26 + $i)] = "A" . $magicAlphabet [$i];
	}
	for($i = 0; $i <= 25; $i ++) {
		$magicAlphabet [(51 + $i)] = "B" . $magicAlphabet [$i];
	}
	$currentSheet = 0;
	
	// //////////////////////////////////////
	// header
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A1', 'from' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'B1', $_GET ['from'] );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'C1', 'to' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'D1', $_GET ['to'] );
	
	// //////////////////////////////////////
	// get all project (generic)
	$allProject = StatisticManagementService::getProjectsStats ();
	
	// //////////////////////////////////////
	// TABLE 1: projects status x projects pf
	$listPF = MTHPlatformManagementService::getMTHPlatforms ();
	// var_dump($listPF); exit;
	$mthPFTab = Array ();
	foreach ( $listPF as $k => $v ) {
		$mthPFTab [$v->getId ()] = $v->getName ();
	}
	
	// TABLE 1: headers
	$currentRow = 3;
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X projects MTH platforms' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	foreach ( $mthPFTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . '' . ($currentRow + 1), '' . $v );
		$colMax = $k;
	}
	$colNone = $colMax + 1;
	$colTotal = $colMax + 2;
	printXLSrowPjStatusKeys ( $objPHPExcel, $currentSheet, $currentRow, $colNone, $colTotal );
	
	// TABLE 1: content
	// all
	$_GET ['group'] = "status";
	$_GET ['isStatus'] = $_GET ['isNotStatus'] = "";
	$projectsStatus = StatisticManagementService::getProjectsStats ();
	// $projectsStatusTab = Array ();
	// foreach ( $projectsStatus as $k => $v ) {
	// $projectsStatusTab [ ( $v ['projects_status'] )] = intval ( $v ['projects_count'] );
	// }
	$projectsStatus = $projectsStatus [0];
	// by grp
	$_GET ['group'] = "mthPF";
	printXLSrowPjStatusContent ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, $currentRow, $projectsStatus, 'pf_ids', null, $currentSheet, $allProject );
	
	// //////////////////////////////////////
	
	// TABLE 2: projects status X keywords
	$currentRow = $currentRow + 12;
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X projects keywords' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listKeywords = KeywordManagementService::getKeywords ();
	// $listKeywords = $listKeywords [0];
	$listKeywordsTab = Array ();
	$listKeywordsDelTab = Array ();
	foreach ( $listKeywords as $k => $v ) {
		$listKeywordsTab [$v->getId ()] = $v->getWord ();
		$listKeywordsDelTab [$v->getId ()] = $v->isDeleted ();
	}
	foreach ( $listKeywordsTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $v );
		if ($listKeywordsDelTab [$k]) {
			$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . ($currentRow + 1) )->applyFromArray ( $styleRed );
		} else {
			$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . ($currentRow + 1) )->applyFromArray ( $styleGeen );
		}
		$colMax = $k;
	}
	$colNone = $colMax + 1;
	$colTotal = $colMax + 2;
	printXLSrowPjStatusKeys ( $objPHPExcel, $currentSheet, $currentRow, $colNone, $colTotal );
	$_GET ['group'] = "keywords";
	printXLSrowPjStatusContent ( $objPHPExcel, $listKeywordsTab, $colNone, $colTotal, $currentRow, $projectsStatus, 'tw_ids', $listKeywordsDelTab, $currentSheet, $allProject );
	
	// //////////////////////////////////////
	
	// TABLE 2 bis: projects status X subkeywords
	$currentRow = $currentRow + 12;
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X projects sub-keywords' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listSubKeywords = KeywordManagementService::getSubKeywords ();
	$listSubKeywordsTab = Array ();
	$listSubKeywordsDelTab = Array ();
	foreach ( $listSubKeywords as $k => $v ) {
		$listSubKeywordsTab [$v->getId ()] = $v->getWord ();
		$listSubKeywordsDelTab [$v->getId ()] = $v->isDeleted ();
	}
	foreach ( $listSubKeywordsTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $v );
		if ($listSubKeywordsDelTab [$k]) {
			$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . ($currentRow + 1) )->applyFromArray ( $styleRed );
		} else {
			$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . ($currentRow + 1) )->applyFromArray ( $styleGeen );
		}
		$colMax = $k;
	}
	$colNone = $colMax + 1;
	$colTotal = $colMax + 2;
	printXLSrowPjStatusKeys ( $objPHPExcel, $currentSheet, $currentRow, $colNone, $colTotal );
	$_GET ['group'] = "subkeywords";
	printXLSrowPjStatusContent ( $objPHPExcel, $listSubKeywordsTab, $colNone, $colTotal, $currentRow, $projectsStatus, 'tw_ids', $listSubKeywordsDelTab, $currentSheet, $allProject );
	
	// //////////////////////////////////////
	
	// TABLE 3: projects status X project demande type
	$currentRow = $currentRow + 12;
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X demande type' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listDemandTypeTab = Array (
			1 => "dt__eqprov",
			2 => "dt__NOT_eqprov",
			3 => "dt__catallo",
			4 => "dt__NOT_catallo",
			5 => "dt__feastu",
			6 => "dt__NOT_feastu",
			7 => "dt__train",
			8 => "dt__NOT_train",
			9 => "dt__data_proc",
			10 => "dt__NOT_data_proc",
			11 => "dt__other",
			12 => "dt__NOT_other" 
	);
	
	$listDemandTypeStrTab = Array (
			1 => "eq. prov",
			2 => "NOT eq. prov.",
			3 => "cat. allow.",
			4 => "NOT cat. allow.",
			5 => "feas. stud.",
			6 => "NOT feas. stud.",
			7 => "train.",
			8 => "NOT train.",
			9 => "data proc.",
			10 => "NOT data proc.",
			11 => "other",
			12 => "NOT other" 
	);
	foreach ( $listDemandTypeTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listDemandTypeStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	// $objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colNone] . '28', 'NONE' );
	printXLSrowPjStatusKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, $colTotal );
	$_GET ['group'] = "type";
	printXLSrowPjStatusContent ( $objPHPExcel, $listDemandTypeTab, - 1, $colTotal, $currentRow, $projectsStatus, null, null, $currentSheet, $allProject );
	
	// //////////////////////////////////////
	
	// TABLE 4: projects status X project funding
	$currentRow = $currentRow + 12;
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X funding' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listFundingTypeTab = Array (
			1 => "f__financed",
			2 => "f__NOT_financed",
			3 => "f__provisioning",
			4 => "f__NOT_provisioning",
			5 => "f__ownsupply",
			6 => "f__NOT_ownsupply",
			7 => "f__notfinanced",
			8 => "f__NOT_notfinanced" 
	);
	
	$listFundingTypeStrTab = Array (
			1 => "financed",
			2 => "NOT financed",
			3 => "provisioning",
			4 => "NOT provisioning",
			5 => "own supply",
			6 => "NOT own supply",
			7 => "not financed",
			8 => "NOT not-financed" 
	);
	foreach ( $listFundingTypeTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listFundingTypeStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	printXLSrowPjStatusKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, $colTotal );
	
	$_GET ['group'] = "financial";
	printXLSrowPjStatusContent ( $objPHPExcel, $listFundingTypeTab, - 1, $colTotal, $currentRow, $projectsStatus, null, null, $currentSheet, $allProject );
	
	// //////////////////////////////////////
	$currentRow = $currentRow + 12;
	// TABLE 5: projects status X sample number
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X sample number' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listSampleNbTab = Array (
			1 => "less_50",
			2 => "51_to_100",
			3 => "101_to_500",
			4 => "more_501",
			5 => "undef" 
	);
	
	$listSampleNbStrTab = Array (
			1 => "less_50",
			2 => "51_to_100",
			3 => "101_to_500",
			4 => "more_501",
			5 => "undefined" 
	);
	foreach ( $listSampleNbTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listSampleNbStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	printXLSrowPjStatusKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, $colTotal );
	
	$_GET ['group'] = "sample_number";
	// $_GET ['isStatus'] = $_GET ['isNotStatus'] = "";
	$tProject = StatisticManagementService::getProjectsStats ();
	printXLSrowPjStatusContent ( $objPHPExcel, $listSampleNbTab, - 1, $colTotal, $currentRow, $projectsStatus, null, null, $currentSheet, $tProject, true );
	
	// //////////////////////////////////////
	$currentRow = $currentRow + 12;
	// TABLE 6: projects status X can be fwd
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X can be forwarded' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listCopartNbTab = Array (
			1 => "can_be_fwd",
			2 => "can_not_be_fwd",
			3 => "undef" 
	);
	
	$listCopartNbStrTab = Array (
			1 => "can be fwd",
			2 => "can't be fwd",
			3 => "undefined" 
	);
	foreach ( $listCopartNbTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listCopartNbStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	printXLSrowPjStatusKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, $colTotal );
	
	$_GET ['group'] = "copartner";
	// $_GET ['isStatus'] = $_GET ['isNotStatus'] = "";
	$tProject = StatisticManagementService::getProjectsStats ();
	// $projectsColTot = StatisticManagementService::getProjectsStats ();
	// $projectsColTot = $projectsColTot [0];
	printXLSrowPjStatusContent ( $objPHPExcel, $listCopartNbTab, - 1, $colTotal, $currentRow, $projectsStatus, null, null, $currentSheet, $tProject );
	
	// //////////////////////////////////////
	$currentRow = $currentRow + 12;
	// TABLE 7: projects status X targetting
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X targeting' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listTargetTab = Array (
			1 => "is_targeted",
			2 => "is_NOT_targeted",
			3 => "undef" 
	);
	
	$listTargetStrTab = Array (
			1 => "targeted",
			2 => "NOT targeted",
			3 => "undefined" 
	);
	foreach ( $listTargetTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listTargetStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	printXLSrowPjStatusKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, $colTotal );
	
	$_GET ['group'] = "targeted";
	// $_GET ['isStatus'] = $_GET ['isNotStatus'] = "";
	$tProject = StatisticManagementService::getProjectsStats ();
	// $projectsColTot = StatisticManagementService::getProjectsStats ();
	// $projectsColTot = $projectsColTot [0];
	printXLSrowPjStatusContent ( $objPHPExcel, $listTargetTab, - 1, $colTotal, $currentRow, $projectsStatus, null, null, $currentSheet, $tProject, true );
	
	// //////////////////////////////////////
	
	// TABLE 3: projects PF X keywords
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet ()->setTitle ( "projects - status" );
}

/**
 */
function buildProjectsPFStatsSheet($objPHPExcel) {
	//
	cleanRequestGET ();
	
	// //////////////////////////////////////
	// SHEET 1: project stats
	global $styleRed;
	global $styleGeen;
	global $magicAlphabet;
	global $currentSheet;
	$currentSheet = 1;
	$objPHPExcel->createSheet ( $currentSheet );
	
	// //////////////////////////////////////
	// header
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A1', 'from' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'B1', $_GET ['from'] );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'C1', 'to' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'D1', $_GET ['to'] );
	
	// //////////////////////////////////////
	// get all project (generic)
	$allProject = StatisticManagementService::getProjectsStats ();
	
	// //////////////////////////////////////
	// TABLE 1: projects status x projects pf
	$listPF = MTHPlatformManagementService::getMTHPlatforms ();
	// var_dump($listPF); exit;
	$mthPFTab = Array ();
	foreach ( $listPF as $k => $v ) {
		$mthPFTab [$v->getId ()] = $v->getName ();
	}
	
	$currentRow = 3;
	
	// // TABLE 1: headers
	// $objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X projects MTH platforms' );
	// $objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// // print PF
	// $colMax = 0;
	// foreach ( $mthPFTab as $k => $v ) {
	// $objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . '' . ($currentRow + 1), '' . $v );
	// $colMax = $k;
	// }
	// $colNone = $colMax + 1;
	// $colTotal = $colMax + 2;
	// printXLSrowPjStatusKeys ( $objPHPExcel, $currentSheet, $currentRow, $colNone, $colTotal );
	
	// // TABLE 1: content
	// // all
	// $_GET ['group'] = "status";
	// $_GET ['isStatus'] = $_GET ['isNotStatus'] = "";
	// $projectsStatus = StatisticManagementService::getProjectsStats ();
	// // $projectsStatusTab = Array ();
	// // foreach ( $projectsStatus as $k => $v ) {
	// // $projectsStatusTab [ ( $v ['projects_status'] )] = intval ( $v ['projects_count'] );
	// // }
	// $projectsStatus = $projectsStatus [0];
	// // by grp
	// $_GET ['group'] = "mthPF";
	// printXLSrowPjStatusContent ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, $currentRow, $projectsStatus, 'pf_ids', null, $currentSheet, $allProject );
	
	// //////////////////////////////////////
	
	$_GET ['group'] = "mthPF status";
	$_GET ['isPlatForm'] = $_GET ['isNotPlatForm'] = "";
	$projectsMthPf = StatisticManagementService::getProjectsStats ();
	
	// TABLE 2: projects status X keywords
	// $currentRow = $currentRow + 12;
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects PF X projects keywords' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listKeywords = KeywordManagementService::getKeywords ();
	// $listKeywords = $listKeywords [0];
	$listKeywordsTab = Array ();
	$listKeywordsDelTab = Array ();
	foreach ( $listKeywords as $k => $v ) {
		$listKeywordsTab [$v->getId ()] = $v->getWord ();
		$listKeywordsDelTab [$v->getId ()] = $v->isDeleted ();
	}
	foreach ( $listKeywordsTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $v );
		if ($listKeywordsDelTab [$k]) {
			$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . ($currentRow + 1) )->applyFromArray ( $styleRed );
		} else {
			$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . ($currentRow + 1) )->applyFromArray ( $styleGeen );
		}
		$colMax = $k;
	}
	$colNone = $colMax + 1;
	$colTotal = $colMax + 2;
	printXLSrowPjPfKeys ( $objPHPExcel, $currentSheet, $currentRow, $colNone, $colTotal, $mthPFTab );
	$_GET ['group'] = "keywords";
	printXLSrowPjPfContent ( $objPHPExcel, $listKeywordsTab, $colNone, $colTotal, $currentRow, $projectsMthPf, 'tw_ids', $listKeywordsDelTab, $currentSheet, null, $mthPFTab );
	
	// //////////////////////////////////////
	
	// TABLE 2 bis: projects status X subkeywords
	$currentRow = $currentRow + (sizeof ( $mthPFTab ) + 5);
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects PF X projects sub-keywords' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listSubKeywords = KeywordManagementService::getSubKeywords ();
	$listSubKeywordsTab = Array ();
	$listSubKeywordsDelTab = Array ();
	foreach ( $listSubKeywords as $k => $v ) {
		$listSubKeywordsTab [$v->getId ()] = $v->getWord ();
		$listSubKeywordsDelTab [$v->getId ()] = $v->isDeleted ();
	}
	foreach ( $listSubKeywordsTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $v );
		if ($listSubKeywordsDelTab [$k]) {
			$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . ($currentRow + 1) )->applyFromArray ( $styleRed );
		} else {
			$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . ($currentRow + 1) )->applyFromArray ( $styleGeen );
		}
		$colMax = $k;
	}
	$colNone = $colMax + 1;
	$colTotal = $colMax + 2;
	printXLSrowPjPfKeys ( $objPHPExcel, $currentSheet, $currentRow, $colNone, $colTotal, $mthPFTab );
	$_GET ['group'] = "subkeywords";
	printXLSrowPjPfContent ( $objPHPExcel, $listSubKeywordsTab, $colNone, $colTotal, $currentRow, $projectsMthPf, 'tw_ids', $listSubKeywordsDelTab, $currentSheet, null, $mthPFTab );
	
	// //////////////////////////////////////
	
	// TABLE 3: projects status X project demande type
	$currentRow = $currentRow + (sizeof ( $mthPFTab ) + 5);
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X demande type' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listDemandTypeTab = Array (
			1 => "dt__eqprov",
			2 => "dt__NOT_eqprov",
			3 => "dt__catallo",
			4 => "dt__NOT_catallo",
			5 => "dt__feastu",
			6 => "dt__NOT_feastu",
			7 => "dt__train",
			8 => "dt__NOT_train",
			9 => "dt__data_proc",
			10 => "dt__NOT_data_proc",
			11 => "dt__other",
			12 => "dt__NOT_other" 
	);
	
	$listDemandTypeStrTab = Array (
			1 => "eq. prov",
			2 => "NOT eq. prov.",
			3 => "cat. allow.",
			4 => "NOT cat. allow.",
			5 => "feas. stud.",
			6 => "NOT feas. stud.",
			7 => "train.",
			8 => "NOT train.",
			9 => "data proc.",
			10 => "NOT data proc.",
			11 => "other",
			12 => "NOT other" 
	);
	foreach ( $listDemandTypeTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listDemandTypeStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	// $objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colNone] . '28', 'NONE' );
	printXLSrowPjPfKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, - 1, $mthPFTab );
	$_GET ['group'] = "type";
	printXLSrowPjPfContent ( $objPHPExcel, $listDemandTypeTab, - 1, - 1, $currentRow, $projectsMthPf, null, null, $currentSheet, null, $mthPFTab, false );
	
	// //////////////////////////////////////
	
	// TABLE 4: projects status X project funding
	$currentRow = $currentRow + (sizeof ( $mthPFTab ) + 5);
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X funding' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listFundingTypeTab = Array (
			1 => "f__financed",
			2 => "f__NOT_financed",
			3 => "f__provisioning",
			4 => "f__NOT_provisioning",
			5 => "f__ownsupply",
			6 => "f__NOT_ownsupply",
			7 => "f__notfinanced",
			8 => "f__NOT_notfinanced" 
	);
	
	$listFundingTypeStrTab = Array (
			1 => "financed",
			2 => "NOT financed",
			3 => "provisioning",
			4 => "NOT provisioning",
			5 => "own supply",
			6 => "NOT own supply",
			7 => "not financed",
			8 => "NOT not-financed" 
	);
	foreach ( $listFundingTypeTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listFundingTypeStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	printXLSrowPjPfKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, - 1, $mthPFTab );
	
	$_GET ['group'] = "financial";
	printXLSrowPjPfContent ( $objPHPExcel, $listFundingTypeTab, - 1, - 1, $currentRow, $projectsMthPf, null, null, $currentSheet, null, $mthPFTab, false );
	
	// //////////////////////////////////////
	$currentRow = $currentRow + (sizeof ( $mthPFTab ) + 5);
	// TABLE 5: projects status X sample number
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X sample number' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listSampleNbTab = Array (
			1 => "less_50",
			2 => "51_to_100",
			3 => "101_to_500",
			4 => "more_501",
			5 => "undef" 
	);
	
	$listSampleNbStrTab = Array (
			1 => "less_50",
			2 => "51_to_100",
			3 => "101_to_500",
			4 => "more_501",
			5 => "undefined" 
	);
	foreach ( $listSampleNbTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listSampleNbStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	printXLSrowPjPfKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, - 1, $mthPFTab );
	
	$_GET ['group'] = "sample_number";
	// $_GET ['isStatus'] = $_GET ['isNotStatus'] = "";
	$tProject = StatisticManagementService::getProjectsStats ();
	printXLSrowPjPfContent ( $objPHPExcel, $listSampleNbTab, - 1, - 1, $currentRow, $projectsMthPf, null, null, $currentSheet, $tProject, $mthPFTab, false );
	
	// //////////////////////////////////////
	$currentRow = $currentRow + (sizeof ( $mthPFTab ) + 5);
	// TABLE 6: projects status X can be fwd
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X can be forwarded' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listCopartNbTab = Array (
			1 => "can_be_fwd",
			2 => "can_not_be_fwd",
			3 => "undef" 
	);
	
	$listCopartNbStrTab = Array (
			1 => "can be fwd",
			2 => "can't be fwd",
			3 => "undefined" 
	);
	foreach ( $listCopartNbTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listCopartNbStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	printXLSrowPjPfKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, - 1, $mthPFTab );
	
	$_GET ['group'] = "copartner";
	// $_GET ['isStatus'] = $_GET ['isNotStatus'] = "";
	$tProject = StatisticManagementService::getProjectsStats ();
	// $projectsColTot = StatisticManagementService::getProjectsStats ();
	// $projectsColTot = $projectsColTot [0];
	printXLSrowPjPfContent ( $objPHPExcel, $listCopartNbTab, - 1, - 1, $currentRow, $projectsMthPf, null, null, $currentSheet, $tProject, $mthPFTab, false );
	
	// //////////////////////////////////////
	$currentRow = $currentRow + (sizeof ( $mthPFTab ) + 5);
	// TABLE 7: projects status X targetting
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . $currentRow, 'projects status X targeting' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 1), 'x' );
	// print PF
	$colMax = 0;
	$listTargetTab = Array (
			1 => "is_targeted",
			2 => "is_NOT_targeted",
			3 => "undef" 
	);
	
	$listTargetStrTab = Array (
			1 => "targeted",
			2 => "NOT targeted",
			3 => "undefined" 
	);
	foreach ( $listTargetTab as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . ($currentRow + 1), '' . $listTargetStrTab [$k] );
		$colMax ++;
	}
	// $colNone = $colMax + 1;
	$colTotal = $colMax + 1;
	printXLSrowPjPfKeys ( $objPHPExcel, $currentSheet, $currentRow, - 1, - 1, $mthPFTab );
	
	$_GET ['group'] = "targeted";
	// $_GET ['isStatus'] = $_GET ['isNotStatus'] = "";
	$tProject = StatisticManagementService::getProjectsStats ();
	// $projectsColTot = StatisticManagementService::getProjectsStats ();
	// $projectsColTot = $projectsColTot [0];
	printXLSrowPjPfContent ( $objPHPExcel, $listTargetTab, - 1, - 1, $currentRow, $projectsMthPf, null, null, $currentSheet, $tProject, $mthPFTab, false );
	
	// //////////////////////////////////////
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet ()->setTitle ( "projects - platforms" );
}

/**
 *
 * @param
 *        	currentSheet
 */
function printXLSrowPjStatusKeys($objPHPExcel, $currentSheet, $currentRow, $colNone, $colTotal) {
	global $magicAlphabet;
	
	if ($colNone > 0)
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colNone] . '' . ($currentRow + 1), 'NONE' );
	if ($colTotal > 0)
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . ($currentRow + 1), 'total' );
	
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 2), 'rejected' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 3), 'waiting' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 4), 'assigned' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 5), 'completed' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 6), 'accepted' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 7), 'running' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 8), 'blocked' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 9), 'archived' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + 10), 'total' );
}

/**
 *
 * @param unknown $objPHPExcel        	
 * @param unknown $currentSheet        	
 * @param unknown $currentRow        	
 * @param unknown $tabMthPf        	
 */
function printXLSrowPjPfKeys($objPHPExcel, $currentSheet, $currentRow, $colNone, $colTotal, $tabMthPf) {
	global $magicAlphabet;
	
	if ($colNone > 0)
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colNone] . '' . ($currentRow + 1), 'NONE' );
	if ($colTotal > 0)
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . ($currentRow + 1), 'total' );
	
	$i = 2;
	foreach ( $tabMthPf as $k => $v ) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + $i), $v );
		$i ++;
	}
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + $i), 'NONE' );
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'A' . ($currentRow + $i + 1), 'total' );
}

/**
 *
 * @param unknown $objPHPExcel        	
 * @param unknown $mthPFTab        	
 * @param unknown $colNone        	
 * @param unknown $colTotal        	
 * @param unknown $currentRow        	
 * @param unknown $projectsStatus        	
 * @param unknown $p_key        	
 */
function printXLSrowPjStatusContent($objPHPExcel, $mthPFTab, $colNone, $colTotal, $currentRow, $projectsStatus, $p_key, $listKeywordsDelTab, $currentSheet, $allProject, $realCount = false) {
	global $magicAlphabet;
	if ($realCount) { $projectsStatus = null; }
	buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 2), $projectsStatus, "rejected", $p_key, $listKeywordsDelTab );
	buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 3), $projectsStatus, "waiting", $p_key, $listKeywordsDelTab );
	buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 4), $projectsStatus, "assigned", $p_key, $listKeywordsDelTab );
	buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 5), $projectsStatus, "completed", $p_key, $listKeywordsDelTab );
	buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 6), $projectsStatus, "accepted", $p_key, $listKeywordsDelTab );
	buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 7), $projectsStatus, "running", $p_key, $listKeywordsDelTab );
	buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 8), $projectsStatus, "blocked", $p_key, $listKeywordsDelTab );
	buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 9), $projectsStatus, "archived", $p_key, $listKeywordsDelTab );
	buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 10), (Array ()), "", $p_key, $listKeywordsDelTab );
	// total x total
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . ($currentRow + 10), '' . $allProject [0] ["projects_count"] );
	// formatting
	$objPHPExcel->getActiveSheet ()->getStyle ( 'A' . '' . ($currentRow + 1) . ':' . $magicAlphabet [$colTotal] . '' . ($currentRow + 1) )->getFont ()->setBold ( true );
	$objPHPExcel->getActiveSheet ()->getStyle ( 'A' . ($currentRow) . ':A' . ($currentRow + 10) )->getFont ()->setBold ( true );
}

/**
 *
 * @param unknown $objPHPExcel        	
 * @param unknown $mthPFTab        	
 * @param unknown $colNone        	
 * @param unknown $colTotal        	
 * @param unknown $currentRow        	
 * @param unknown $projectsStatus        	
 * @param unknown $p_key        	
 * @param unknown $listKeywordsDelTab        	
 * @param unknown $currentSheet        	
 * @param unknown $allProject        	
 * @param unknown $tabMthPf        	
 */
function printXLSrowPjPfContent($objPHPExcel, $mthPFTab, $colNone, $colTotal, $currentRow, $projectsStatus, $p_key, $listKeywordsDelTab, $currentSheet, $allProject, $tabMthPf, $lineTot = true) {
	global $magicAlphabet;
	
	$i = 2;
	$stringTabKeys = "";
	foreach ( $tabMthPf as $k => $v ) {
		buildPfXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + $i), null, $k, $p_key, $listKeywordsDelTab, null );
		$stringTabKeys .= "," . $k;
		$i ++;
	}
	// row NONE
	buildPfXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + $i), null, "_NONE_", $p_key, $listKeywordsDelTab, $stringTabKeys );
	// TODO NONE x NONE
	// $objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . ($currentRow + $i), '' . "??" );
	$i ++;
	// row TOT
	if ($lineTot) {
		buildPfXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + $i), null, "_ALL_", $p_key, $listKeywordsDelTab, $stringTabKeys );
	}
	// tot x tot
	if (! is_null ( $allProject ) && $colTotal > 0) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . ($currentRow + $i), '' . $allProject [0] ["projects_count"] );
	} else if ($colTotal > 0) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . ($currentRow + $i), '' );
	}
	// formatting
	if ($colTotal > 0) {
		$objPHPExcel->getActiveSheet ()->getStyle ( 'A' . '' . ($currentRow + 1) . ':' . $magicAlphabet [$colTotal] . '' . ($currentRow + 1) )->getFont ()->setBold ( true );
	} else {
		$objPHPExcel->getActiveSheet ()->getStyle ( 'A' . '' . ($currentRow + 1) . ':' . $magicAlphabet [15] . '' . ($currentRow + 1) )->getFont ()->setBold ( true );
	}
	$objPHPExcel->getActiveSheet ()->getStyle ( 'A' . ($currentRow) . ':A' . ($currentRow + $i) )->getFont ()->setBold ( true );
	
	// buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 2), $projectsStatus, "rejected", $p_key, $listKeywordsDelTab );
	// buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 3), $projectsStatus, "waiting", $p_key, $listKeywordsDelTab );
	// buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 4), $projectsStatus, "assigned", $p_key, $listKeywordsDelTab );
	// buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 5), $projectsStatus, "completed", $p_key, $listKeywordsDelTab );
	// buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 6), $projectsStatus, "accepted", $p_key, $listKeywordsDelTab );
	// buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 7), $projectsStatus, "running", $p_key, $listKeywordsDelTab );
	// buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 8), $projectsStatus, "blocked", $p_key, $listKeywordsDelTab );
	// buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 9), $projectsStatus, "archived", $p_key, $listKeywordsDelTab );
	// buildStatusXdataLine ( $objPHPExcel, $mthPFTab, $colNone, $colTotal, '' . ($currentRow + 10), (Array ()), "", $p_key, $listKeywordsDelTab );
	// // total x total
	// $objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . ($currentRow + 10), '' . $allProject [0] ["projects_count"] );
}

/**
 */
function cleanRequestGET() {
	// //////////////////////////////////////
	// p00n regular $_GET
	$from = $_GET ['from'];
	$to = $_GET ['to'];
	foreach ( $_GET as $k => $v ) {
		$_GET [$k] = null;
		unset ( $_GET [$k] );
	}
	$_GET ['from'] = $from;
	$_GET ['to'] = $to;
}

/**
 *
 * @param unknown $objPHPExcel        	
 * @param unknown $mthPFTab        	
 * @param unknown $colNone        	
 * @param unknown $colTotal        	
 * @param unknown $lineIndex        	
 */
function buildStatusXdataLine($objPHPExcel, $mthPFTab, $colNone, $colTotal, $lineIndex, $tabSum, $indexSum, $tabKey, $tabColor = null) {
	$_GET ['isStatus'] = $indexSum;
	$projectsWaiting = StatisticManagementService::getProjectsStats ();
	$projectWaitingTab = Array ();
	if (is_null ( $tabKey )) {
		foreach ( $mthPFTab as $k => $v ) {
			$projectWaitingTab [$k] = intval ( $projectsWaiting [0] [$v] );
		}
	} else {
		foreach ( $projectsWaiting as $k => $v ) {
			$projectWaitingTab [intval ( $v [$tabKey] )] = intval ( $v ['projects_count'] );
		}
	}
	$sum = 0;
	global $magicAlphabet;
	global $currentSheet;
	global $styleRed;
	global $styleGeen;
	foreach ( $mthPFTab as $k => $v ) {
		if (array_key_exists ( $k, $projectWaitingTab )) {
			$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . '' . $lineIndex, '' . $projectWaitingTab [$k] );
			$sum += $projectWaitingTab [$k];
		} else {
			$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . '' . $lineIndex, '' . 0 );
		}
		if (! is_null ( $tabColor )) {
			if (array_key_exists ( $k, $tabColor )) {
				if ($tabColor [$k]) {
					$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . '' . $lineIndex )->applyFromArray ( $styleRed );
				} else {
					$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . '' . $lineIndex )->applyFromArray ( $styleGeen );
				}
			}
		}
	}
	// NONE
	if (array_key_exists ( 0, $projectWaitingTab )) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colNone] . '' . $lineIndex, $projectWaitingTab [0] );
		$sum += $projectWaitingTab [0];
	} else if ($colNone >= 0) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colNone] . '' . $lineIndex, '' . 0 );
	}
	// TOTAL
	if (! is_null ( $tabSum )) {
		if (array_key_exists ( $indexSum, $tabSum )) {
			$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . $lineIndex, $tabSum [$indexSum] );
		} else {
			$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . $lineIndex, '' . 0 );
		}
	} else {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . $lineIndex, '' . $sum );
	}
}
function buildPfXdataLine($objPHPExcel, $mthPFTab, $colNone, $colTotal, $lineIndex, $tabSum, $indexSum, $tabKey, $tabColor = null, $tabKeys = null) {
	if ($indexSum == "_ALL_") {
		$_GET ['isPlatForm'] = $tabKeys;
		$_GET ['isNotPlatForm'] = "";
	} else if ($indexSum == "_NONE_") {
		$_GET ['isPlatForm'] = "";
		$_GET ['isNotPlatForm'] = $tabKeys;
	} else {
		$_GET ['isPlatForm'] = $indexSum;
		$_GET ['isNotPlatForm'] = "";
	}
	
	$projectsWaiting = StatisticManagementService::getProjectsStats ();
	$projectWaitingTab = Array ();
	if (is_null ( $tabKey )) {
		foreach ( $mthPFTab as $k => $v ) {
			$projectWaitingTab [$k] = intval ( $projectsWaiting [0] [$v] );
		}
	} else {
		foreach ( $projectsWaiting as $k => $v ) {
			$projectWaitingTab [intval ( $v [$tabKey] )] = intval ( $v ['projects_count'] );
		}
	}
	$sum = 0;
	global $magicAlphabet;
	global $currentSheet;
	global $styleRed;
	global $styleGeen;
	foreach ( $mthPFTab as $k => $v ) {
		if (array_key_exists ( $k, $projectWaitingTab )) {
			$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . '' . $lineIndex, '' . $projectWaitingTab [$k] );
			$sum += $projectWaitingTab [$k];
		} else {
			$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$k] . '' . $lineIndex, '' . 0 );
		}
		if (! is_null ( $tabColor )) {
			if (array_key_exists ( $k, $tabColor )) {
				if ($tabColor [$k]) {
					$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . '' . $lineIndex )->applyFromArray ( $styleRed );
				} else {
					$objPHPExcel->getActiveSheet ()->getStyle ( $magicAlphabet [$k] . '' . $lineIndex )->applyFromArray ( $styleGeen );
				}
			}
		}
	}
	// NONE
	if (array_key_exists ( 0, $projectWaitingTab )) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colNone] . '' . $lineIndex, $projectWaitingTab [0] );
		$sum += $projectWaitingTab [0];
	} else if ($colNone >= 0) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colNone] . '' . $lineIndex, '' . 0 );
	}
	// TOTAL
	if (! is_null ( $tabSum ) && ($colTotal > 0)) {
		if (array_key_exists ( $indexSum, $tabSum )) {
			$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . $lineIndex, $tabSum [$indexSum] );
		} else {
			$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . $lineIndex, '' . 0 );
		}
	} else if (($colTotal > 0)) {
		$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( $magicAlphabet [$colTotal] . '' . $lineIndex, '' . $sum );
	}
}

/**
 *
 * @param unknown $objPHPExcel        	
 * @param unknown $lineIndex        	
 * @param unknown $arrayData        	
 * @param unknown $arrayKey        	
 */
function buildCountUserXLaboTypeLine($objPHPExcel, $lineIndex, $arrayData, $arrayKey) {
	global $currentSheet;
	$objPHPExcel->setActiveSheetIndex ( $currentSheet )->setCellValue ( 'B' . '' . $lineIndex, $arrayData [$arrayKey] );
}
