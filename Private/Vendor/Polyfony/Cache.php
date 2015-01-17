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

class Cache {

	// hardcoded because we need the path very early to check is the request is cached, at the time Config is not available yet
	private static $_root = '../Private/Storage/Cache/';

	public static function has($variable) {
		
		// secure the variable name
		$variable = \Polyfony\Format::fsSafe($variable);
		// set the variable path
		$path = self::$_root . $variable;
		// if the file exists 
		if(file_exists($path)) {
			// if it expired
			if(filemtime($path) < time()) {
				// the file expired, remove it
				unlink($path);
				// we don't have that element
				return(false);
			}
			// the cache file has not expired yet
			else {
				// we do have the file
				return(true);
			}
		}

	}
	

	public static function put($variable, $value=null, $overwrite=false, $lifetime=false) {
	
		// already exists and no overwrite
		if(self::has($variable) && !$overwrite) {
			// throw an exception
			\Polyfony\Exception("{$variable} already exists in the store.");
		}
		// secure the variable name
		$variable = \Polyfony\Format::fsSafe($variable);
		// store it
		file_put_contents(self::$_root . $variable, json_encode($value));
		// compute the expiration time
		$expire = $lifetime ? time() + $lifetime : time() * 365 * 24 * 3600;
		// alter the modification time
		touch(self::$_root . $variable, $expire);
		// return status
		return(self::has($variable));
		
	}
	

	public static function get($variable) {
		
		// doesn't exist in the store
		if(!self::has($variable)) {
			// throw an exception
			\Polyfony\Exception("{$variable} does not exist in the store.");
		}
		// secure the variable name
		$variable = \Polyfony\Format::fsSafe($variable);
		// return it
		return(json_decode(file_get_contents(self::$_root . $variable), true));
		
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
