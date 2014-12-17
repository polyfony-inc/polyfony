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

class pfSecurity {
	
	public static function secure($module=null,$level_bypass=null) {
		
		// if we have a security cookie we authenticate with it
		pfRequest::cookie(pfConfig::get('security','cookie')) ? self::authenticate();
		
		// if we have a post and posted a login, we log in with it
		pfRequest::isPost() and pfRequest::post(pfConfig::get('security','login')) ? self::login();
		
		// if we have the module
		self::$_granted = self::hasModule($module) ? true : false;
		
		// if we have the bypass level
		self::$_granted = self::hasLevel($level) ? true : false;
		
		// and now we check if we have the proper rights
		self::$_granted 
		
	}
	
	public static function authenticate() {
		
	}
	
	public static function login($login,$password) {

	}
	
	public static function getPassword($string) {
		
	}
	
	public static function hasLevel($level=null) {
		
	}
	
	public static function hasModule($module=null,$level_bypass=null) {
		
	}
	
}	

?>