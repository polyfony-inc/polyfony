<?php
/**
 * PHP Version 5
 * Helper for filesystem operations
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Filesystem {
	
	
	public static function isNormalName($string) {
		
		// true if not starting with a dot, false otherwise
		return((substr($string,0,1) != '.' ) ? true : false);
		
	}
	
	
	public static function getFolders($path, $filter_callback=null) {
		
	}
	
	
	public static function getFiles($path ,$filter_callback=null) {
		
	}
	
}

?>