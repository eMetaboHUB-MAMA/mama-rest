<?php
// ////////////////////////////////////////////////////////////////////////////
// FUNCTIONS
/**
 *
 * @param unknown $haystack        	
 * @param unknown $needle        	
 * @return boolean
 */
function endsWith($haystack, $needle) {
	// search forward starting from end minus needle length characters
	return $needle === "" || (($temp = strlen ( $haystack ) - strlen ( $needle )) >= 0 && strpos ( $haystack, $needle, $temp ) !== false);
}

// ////////////////////////////////////////////////////////////////////////////
// CORE
//
$dir = '.';
$files = scandir ( $dir );

foreach ( $files as $file ) {
	if (endsWith ( $file, "ManagementServiceTest.php" )) {
		echo "running $file \n";
		echo `phpunit --bootstrap ../vendor/autoload.php $file`;
	}
}

/**
 *
 * @author Nils Paulhe
 *        
 */
class AllTestsTest extends PHPUnit_Framework_TestCase {
	
	/**
	 */
	public function testOK() {
		
		// check if old is deleted
		$this->assertEquals ( true, true, "[error] unit test failled " );
	}
}