<?php

/**
 * @param unknown $haystack
 * @param unknown $needle
 * @return boolean
 */
function startsWith($haystack, $needle) {
	// search backwards starting from haystack length characters from the end
	return $needle === "" || strrpos ( $haystack, $needle, - strlen ( $haystack ) ) !== FALSE;
}

/**
 *
 * @param unknown $haystack        	
 * @param unknown $needle        	
 * @return boolean
 */
function endsWith($haystack, $needle) {
	// search forward starting from end minus needle length characters
	return $needle === "" || (($temp = strlen ( $haystack ) - strlen ( $needle )) >= 0 && strpos ( $haystack, $needle, $temp ) !== FALSE);
}