<?php
/**
 * PHP Version 5
 * Sanitization helper, when validation would be cause to much overweight
 * just sanitize your string with this class before accepting them
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Tools {
	
	
	public static function isFile($string) {
		
		return((substr($string,0,1) != '.' ) ? true : false);
		
	}
	
}

?>