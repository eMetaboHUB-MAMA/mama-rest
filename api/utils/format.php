<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// FORMAT FUNCTIONS
function formatResponse($request, $response, $args, $data) {
	$format = getFormat ();
	
	// check client accept
	$headerValueArray = $request->getHeader ( 'Accept' );
	if (in_array ( "application/json", $headerValueArray ) || $format == "json") {
		header ( "Content-Type: application/json" );
		echo json_encode ( $data );
		exit ();
	} else if (in_array ( "application/xml", $headerValueArray ) || $format == "xml") {
		header ( "Content-Type: application/xml" );
		echo xml_encode ( $data );
		exit ();
	} else {
		$response = $response->withHeader ( 'Content-type', 'text/pain' );
		dump_array_to_response ( "", $data, $response );
		return $response;
	}
}
function getFormat() {
	$format = "";
	if (isset ( $_GET ["format"] ) && $_GET ["format"] != null) {
		$format = $_GET ["format"];
	}
	return $format;
}

// //////////////////////////////////////////////////////////////////////////////////////////////
// DUMPER FUNCTIONS
function dump_array_to_response($prefix, $array, $response) {
	if (is_object ( $array )) {
		dump_array_to_response ( $prefix, ( array ) $array, $response );
	} else if (is_array ( $array )) {
		foreach ( $array as $key => $value ) {
			if (is_array ( $value )) {
				if ($prefix != "")
					$prefix .= ".";
				dump_array_to_response ( $prefix . $key, $value, $response );
			} else {
				if ($prefix != "" && ! (substr ( $prefix, - 1 ) == "."))
					$prefix .= ".";
				if ($value instanceof DateTime) {
					$value = date_format ( $value, 'Y-m-d H:i:s' );
				}
				$response->write ( $prefix . $key . ":\t" . $value . "\n" );
			}
		}
	}
}
function xml_encode($mixed, $domElement = null, $DOMDocument = null) {
	if (is_object ( $mixed )) {
		$mixed = get_object_vars ( $mixed );
	}
	if (is_null ( $DOMDocument )) {
		$DOMDocument = new DOMDocument ();
		$DOMDocument->formatOutput = true;
		xml_encode ( $mixed, $DOMDocument, $DOMDocument );
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
					}
				} else {
					$plural = $DOMDocument->createElement ( $index );
					$domElement->appendChild ( $plural );
					$node = $plural;
					if (! (rtrim ( $index, 's' ) === $index)) {
						$singular = $DOMDocument->createElement ( rtrim ( $index, 's' ) );
						$plural->appendChild ( $singular );
						$node = $singular;
					}
				}
				
				xml_encode ( $mixedElement, $node, $DOMDocument );
			}
		} else {
			$domElement->appendChild ( $DOMDocument->createTextNode ( $mixed ) );
		}
	}
}