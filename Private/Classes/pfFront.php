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
 
class pfFront {
	
	// upon construction
	public function __construct() {
		
		// init the request
		// detect context CLI/WEB and set the proper url
		pfRequest::init();
		
		// init the configuration
		// detect env and load proper .ini files
		pfConfig::init();
		
		// now route !
		pfRouter::route();
		
	}
	
}


?>