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

namespace Polyfony;

class Runtime {
	
	protected static $_runtimes;
	
	public static function set($bundle,$key,$value=null) {
		
		// if only bundle + one parameters set the whole bundle, else set a key of the bundle
		$value != null ? self::$_runtimes[$bundle][$key] = $value : self::$_runtimes[$bundle] = $key;
		
	}
	
	public static function get($bundle,$key=null) {
	

	
	}
	
}

?>