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

namespace Polyfony\Store;

class Filesystem implements StoreInterface {

	// where to store
	protected static $_root = '../Private/Storage/Store/';

	public static function has($variable) {
		
		// secure the variable name
		$variable = \Polyfony\Format::fsSafe($variable);
		// if it exists
		return(file_exists(self::$_root . $variable));
		
	}
	

	public static function put($variable, $value=null, $overwrite=false) {
	
		// already exists and no overwrite
		if(self::has($variable) && !$overwrite) {
			// return false
			return(false);
		}
		// secure the variable name
		$variable = \Polyfony\Format::fsSafe($variable);
		// store it
		file_put_contents(self::$_root . $variable, serialize($value));
		// return status
		return(self::has($variable));
		
	}
	

	public static function get($variable) {
		
		// doesn't exist in the store
		if(!self::has($variable)) {
			// return false
			return(false);	
		}
		// secure the variable name
		$variable = \Polyfony\Format::fsSafe($variable);
		// return it
		return(unserialize(file_get_contents(self::$_root . $variable)));
		
	}
	
	
	public static function remove($variable) {
		
		// doesn't exist in the store
		if(!self::has($variable)) {
			// return false
			return(false);	
		}
		// secure the variable name
		$variable = \Polyfony\Format::fsSafe($variable);
		// return it
		unlink(self::$_root . $variable);
		// return opposite of presence of the object
		return(!self::has($variable));
		
	}


}

?>
