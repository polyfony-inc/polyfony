<?php
/**
 * PHP Version 5
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */
 
class pfLoader {
	
	// upon construction
	public function __construct() {
		// register method loader as the autoload
		spl_autoload_register(array($this,'loader'),true);
	}
	
	// method is call everytime we try to instanciate a class
	private function loader($class) {
		// define a array of possibilities
		$sources = array(
			"../Private/Vendor/Polyfony/{$class}.php",
			"../Private/Vendor/{$class}/{$class}.php",
			"../Private/Vendor/{$class}/{$class}.class.php"
		);
		// for each possibility
		foreach($sources as $file) {
			// if the file exists
			if(file_exists($file)) {
				// include it
				include_once($file);
				// stop here
				return;
			}
		}
	}

}


?>