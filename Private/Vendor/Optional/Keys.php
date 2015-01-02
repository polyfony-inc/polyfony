<?php
/**
 * PHP Version 5
 * Keys generation and comparison helper
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Optional;

class Keys {

	// the salt used to secure your keys, IT IS VERY IMPORTANT THAT YOU CHANGE IT !
	private static $_salt = 'W292CD07H028XB6TBZ92L0UN567892BVF6RV2087B0D6GJ*Tg6!Smx-2dS';

	// generate a key
	public static function generate($mixed=null) {
		// create a sha1 signature of the array with a salt
		$hash = sha1(json_encode(array($mixed,self::$_salt), JSON_NUMERIC_CHECK));
		// get last 10 and first 10 chars together, convert to uppercase, return the key
		return(strtoupper(substr($hash, -10) . substr($hash, 0, 10)));
	}

	// compare a key with a new dynamically generated one
	public static function compare($key=null, $mixed=null) {
		// if no key is provided
		if(!$key || strlen($key) != 20) {
			// return false
			return(false);	
		}
		// if keys do match
		return(self::generate($mixed) == $key ?: false);
	}

}

?>
