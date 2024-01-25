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
/**
 *
 * @param unknown $request        	
 * @param unknown $response        	
 * @param unknown $args        	
 * @param unknown $data        	
 * @param string $ultraPrune        	
 * @return unknown
 */
function formatResponse($request, $response, $args, $data, $ultraPrune = false) {
	$format = getFormat ();
	
	// check client accept
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/json", $headerValueArray ) || $format == "json") {
		header ( "Content-Type: application/json" );
		echo json_encode_special ( $data, true, $ultraPrune );
		exit ();
	} else if (in_array ( "application/xml", $headerValueArray ) || $format == "xml") {
		header ( "Content-Type: application/xml" );
		echo xml_encode ( $data, null, null, $ultraPrune );
		exit ();
	} else {
		$response = $response->withHeader ( 'Content-type', 'text/pain' );
		dump_array_to_response ( "", $data, $response, $ultraPrune );
		return $response;
	}
}
/**
 *
 * @return string|unknown
 */
function getFormat() {
	$format = "";
	if (isset ( $_GET ["format"] ) && $_GET ["format"] != null) {
		$format = $_GET ["format"];
	}
	return $format;
}

// //////////////////////////////////////////////////////////////////////////////////////////////
// DUMPER FUNCTIONS
/**
 *
 * @param unknown $value        	
 * @param string $root        	
 * @param unknown $ultraPrune        	
 * @return string|unknown
 */
function json_encode_special($value, $root = true, $ultraPrune) {
	if (is_object ( $value ) && method_exists ( $value, 'getJsonData' )) {
		$value = $value->getJsonData ( $ultraPrune );
		if ($root)
			return json_encode ( $value );
		else
			return $value;
	} else if (is_array ( $value )) {
		$newObj = Array ();
		foreach ( $value as $k => $v ) {
			if (is_object ( $v ))
				$v = json_encode_special ( $v, false, $ultraPrune );
			else if (is_bool ( $v ))
				$v = boolval ( $v );
			else if (is_int ( $v ) || $v == intval ( $v ) . "")
				$v = intval ( $v );
				// else if (is_array ( $v ))
				// $v = json_encode_special ( $v, false );
			else if (is_array ( $v )) {
				$newObj2 = Array ();
				foreach ( $v as $k2 => $v2 ) {
					if (is_object ( $v2 ))
						$v2 = json_encode_special ( $v2, false, $ultraPrune );
					else if (is_bool ( $v2 ))
						$v2 = boolval ( $v2 );
					else if (is_int ( $v2 ) || $v2 == intval ( $v2 ) . "")
						$v2 = intval ( $v2 );
					$newObj2 [$k2] = $v2;
				}
				$v = $newObj2;
			}
			$newObj [$k] = $v;
		}
		$value = $newObj;
		return json_encode ( $value );
	}
	return json_encode ( $value );
}
/**
 *
 * @param unknown $prefix        	
 * @param unknown $array        	
 * @param unknown $response        	
 * @param unknown $ultraPrune        	
 */
function dump_array_to_response($prefix, $array, $response, $ultraPrune) {
	if (is_object ( $array )) {
		if (method_exists ( $array, 'getArrayData' ))
			$array = $array->getArrayData ( $ultraPrune );
		else
			$array = get_object_vars ( $array );
		dump_array_to_response ( $prefix, ( array ) $array, $response, $ultraPrune );
	} else if (is_array ( $array )) {
		foreach ( $array as $key => $value ) {
			if (is_array ( $value )) {
				if ($prefix != "" && ! (substr ( $prefix, - 1 ) == "."))
					$prefix .= ".";
				dump_array_to_response ( $prefix . $key, $value, $response, $ultraPrune );
			} else if (is_object ( $value )) {
				if ($prefix != "" && ! (substr ( $prefix, - 1 ) == "."))
					$prefix .= ".";
				dump_array_to_response ( $prefix . $key, $value, $response, $ultraPrune );
			} else {
				if ($prefix != "" && ! (substr ( $prefix, - 1 ) == "."))
					$prefix .= ".";
				if ($value instanceof DateTime) {
					$value = date_format ( $value, 'Y-m-d H:i:s' );
				}
				if (is_bool ( $value )) {
					if ($value)
						$value = "true";
					else
						$value = "false";
				}
				$response->write ( $prefix . $key . ":\t" . $value . "\n" );
			}
		}
	}
}
/**
 *
 * @param unknown $Data        	
 * @return unknown[]|NULL[][]|NULL[]|unknown[]|unknown
 */
function object2array($Data) {
	if (is_object ( $Data )) {
		$ret = [ ];
		foreach ( get_object_vars ( $Data ) as $key => $val ) {
			$ret [$key] = object2array ( $val );
		}
		return $ret;
	} elseif (is_array ( $Data )) {
		$ret = [ ];
		foreach ( $Data as $key => $val ) {
			$ret [$key] = object2array ( $val );
		}
		return $ret;
	} else {
		return $Data;
	}
}
/**
 *
 * @param unknown $mixed        	
 * @param unknown $domElement        	
 * @param unknown $DOMDocument        	
 * @param unknown $ultraPrune        	
 */
function xml_encode($mixed, $domElement = null, $DOMDocument = null, $ultraPrune) {
	if (is_object ( $mixed )) {
		// $mixed = xmlobj2arr($mixed);
		if (method_exists ( $mixed, 'getArrayData' ))
			$mixed = $mixed->getArrayData ( $ultraPrune );
		else
			$mixed = get_object_vars ( $mixed );
	}
	if (is_null ( $DOMDocument )) {
		$DOMDocument = new DOMDocument ();
		$DOMDocument->formatOutput = true;
		xml_encode ( $mixed, $DOMDocument, $DOMDocument, $ultraPrune );
		echo $DOMDocument->saveXML ();
	} else {
		if (is_array ( $mixed )) {
			foreach ( $mixed as $index => $mixedElement ) {
				if (is_int ( $index )) {
					if ($index === 0) {
						$node = $domElement;
						if (is_array ( $mixedElement ) && array_key_exists ( "id", $mixedElement )) {
							$node->setAttribute ( "id", $mixedElement ["id"] );
						}
					} else {
						$node = $DOMDocument->createElement ( $domElement->tagName );
						$domElement->parentNode->appendChild ( $node );
						if (is_array ( $mixedElement ) && array_key_exists ( "id", $mixedElement )) {
							$node->setAttribute ( "id", $mixedElement ["id"] );
						}
						// if (is_array ( $mixedElement ) && array_key_exists ( "success", $mixedElement )) {
						// $node->setAttribute ( "success", $mixedElement ["success"] );
						// }
					}
				} else {
					$plural = $DOMDocument->createElement ( $index );
					$domElement->appendChild ( $plural );
					$node = $plural;
					if (! (rtrim ( $index, 's' ) === $index) && ($index != "status" && $index != "success")) {
						$singular = $DOMDocument->createElement ( rtrim ( $index, 's' ) );
						$plural->appendChild ( $singular );
						$node = $singular;
					}
					if ($index == "success") {
						$successV = "true";
						if (! $mixed ['success']) {
							$successV = "false";
						}
						$node->setAttribute ( "value", $successV );
					}
				}
				
				xml_encode ( $mixedElement, $node, $DOMDocument, $ultraPrune );
			}
		} else {
			$domElement->appendChild ( $DOMDocument->createTextNode ( $mixed ) );
		}
	}
}
/**
 *
 * @param unknown $type        	
 * @param unknown $data        	
 */
function getXLSfile($type, $data) {
	
	// Create new PHPExcel object
	$objPHPExcel = new Spreadsheet ();
	// Set document properties
	$objPHPExcel->getProperties ()->setCreator ( "MAMA - Bot (>*.*)>" );
	$objPHPExcel->getProperties ()->setLastModifiedBy ( "MAMA - Bot (>*.*)>" );
	$objPHPExcel->getProperties ()->setTitle ( "MAMA - " . $type );
	$objPHPExcel->getProperties ()->setSubject ( "MAMA - " . $type );
	$objPHPExcel->getProperties ()->setDescription ( "list of all " . $type . " ref. in MAMA API" );
	$objPHPExcel->getProperties ()->setKeywords ( "MAMA " . $type );
	$objPHPExcel->getProperties ()->setCategory ( "XLS file" );
	
	// Add some data
	if ($type == "projects") {
		// header
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'A1', '#id' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'B1', 'title' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'C1', 'status' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'D1', 'owner' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'E1', 'in charge' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'F1', 'involved' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'G1', 'created' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'H1', 'updated' );
		// project demand type
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'I1', 'demand_type' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'J1', 'samples_number' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'K1', 'thematic_words' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'L1', 'sub_thematic_words' );
		// project info
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'M1', 'targeted_untargeted' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'N1', 'mth_plateforms' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'O1', 'can_be_fwd_copartner' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'P1', 'scientific_context' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'Q1', 'scientific_context_file' );
		// financing
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'R1', 'financing' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'S1', 'financing_type' );
		$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'T1', 'financing_other' );
		// more data? filter?
		$objPHPExcel->getActiveSheet ()->getStyle ( 'A1:T1' )->getFont ()->setBold ( true );
		
		// content
		$i = 2;
		foreach ( $data as $key => $value ) {
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'A' . $i, $value->getIdLong () );
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'B' . $i, $value->getTitle () );
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'C' . $i, $value->getStatus () );
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'D' . $i, $value->getOwner ()->getFullName () );
			if ($value->getAnalystInCharge () != null) {
				$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'E' . $i, $value->getAnalystInCharge ()->getFullName () );
			} else {
				$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'E' . $i, '' );
			}
			$involvedT = "";
			if ($value->getAnalystsInvolved () != null) {
				foreach ( $value->getAnalystsInvolved () as $k => $v ) {
				    if ($involvedT != "") {
						$involvedT .= ", ";
				    }
					$involvedT .= $v->getFullName ();
				}
			}
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'F' . $i, $involvedT );
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'G' . $i, $value->getCreated ()->format ( 'Y-m-d H:i:s' ) );
			if ($value->getUpdated () != null) {
				$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'H' . $i, $value->getUpdated ()->format ( 'Y-m-d H:i:s' ) );
			} else {
				$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'H' . $i, '' );
			}
			// project own data
			$demandType = "";
			if ($value->getDemandTypeEqProvisioning())   { $demandType .= "Provision of equipment; "; }
			if ($value->getDemandTypeCatalogAllowance()) { $demandType .= "Provision of service - in routine; "; }
			if ($value->getDemandTypeFeasibilityStudy()) { $demandType .= "Feasibility study; "; }
			if ($value->getDemandTypeDataProcessing())   { $demandType .= "Data processing and analysis; "; }
			if ($value->getDemandTypeTraining())         { $demandType .= "Training; "; }
			if ($value->getDemandTypeOther())            { $demandType .= "Other; "; }
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'I' . $i, $demandType );
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'J' . $i, $value->getSamplesNumberAsString() );
			$cloudWords = "";
			foreach ( $value->getThematicWords() as $k => $v ) {
			    $cloudWords .= $v->getWord() . "; ";
			}
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'K' . $i, $cloudWords );
			$subCloudWords = "";
			foreach ( $value->getSubThematicWords() as $k => $v ) {
			    $subCloudWords .= $v->getWord() . "; ";
			}
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'L' . $i, $subCloudWords );
			// project info
			$targeted = "";
			if ($value->getTargeted() != null) { $targeted = ($value->getTargeted()) ? "targeted" : "untargeted"; }
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'M' . $i, $targeted );
			$mthPF = "";
			foreach ( $value->getMthPlatforms() as $k => $v ) {
			    $mthPF .= $v->getName() . "; ";
			}
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'N' . $i, $mthPF );
			$canBeFwdCopart = "";
			if ($value->getCanBeForwardedToCoPartner() != null) { $canBeFwdCopart = ($value->getCanBeForwardedToCoPartner()) ? "yes" : "no"; }
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'O' . $i, $canBeFwdCopart );
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'P' . $i, $value->getScientificContext() );
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'Q' . $i, (preg_replace ( '/^(\d+)-/i', "", $value->getScientificContextFile() )) );
			// financing
			$financing = "";
			if ($value->getFinancialContextIsProjectFinanced())       { $financing .= "Financed with accepted project; "; }
			if ($value->getFinancialContextIsProjectInProvisioning()) { $financing .= "Submitted project; "; }
			if ($value->getFinancialContextIsProjectOnOwnSupply())    { $financing .= "Own resources; "; }
			if ($value->getFinancialContextIsProjectNotFinanced())    { $financing .= "No financing; "; }
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'R' . $i, $financing );
			$financingType = "";
			if ($value->getFinancialContextIsProjectEU())       { $financingType .= "EU; "; }
			if ($value->getFinancialContextIsProjectANR())      { $financingType .= "ANR; "; }
			if ($value->getFinancialContextIsProjectNational()) { $financingType .= "National; "; }
			if ($value->getFinancialContextIsProjectRegional()) { $financingType .= "Regional; "; }
			if ($value->getFinancialContextIsProjectCompagnyTutorship())      { $financingType .= "Private company; "; }
			if ($value->getFinancialContextIsProjectInternationalOutsideEU()) { $financingType .= "International outside EU; "; }
			if ($value->getFinancialContextIsProjectOwnResourcesLaboratory()) { $financingType .= "Own resources laboratory; "; }
			if ($value->getFinancialContextIsProjectOther())                  { $financingType .= "Other; "; }
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'S' . $i, $financingType );
			$objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( 'T' . $i, $value->getFinancialContextIsProjectOtherValue() );
			// more data? filter?
			$i ++;
		}
		
		// formatting
		foreach ( range ( 'A', 'H' ) as $columnID ) {
			$objPHPExcel->getActiveSheet ()->getColumnDimension ( $columnID )->setAutoSize ( true );
		}
		
		// Rename worksheet
		$objPHPExcel->getActiveSheet ()->setTitle ( $type );
		
		// Redirect output to a client’s web browser (Excel5)
		header ( 'Content-Type: application/vnd.ms-excel' );
		header ( 'Content-Disposition: attachment;filename="' . $type . '.xls"' );
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
}
