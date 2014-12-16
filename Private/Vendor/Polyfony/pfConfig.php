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

class pfConfig {
	
	protected static $_environment;
	protected static $_config;
	
	public static function init() {
	
		// depending on the context, detect environment differently
		pfRequest::getContext() == 'CLI' ? self::detectFromCLI() : self::detectFromHTTP();
		
	}
	
	public static function load() {
		
	}
	
	private static function detectFromCLI() {
	
		// use first command line argument, or prod
		self::$_environment = isset($_ARGV[1]) ? ucfirst(strtolower($_ARGV[1])) : 'Prod';
		
	}
	
	private static function detectFromHTTP() {
	
		
		
	}
	
	public static function set($group,$key,$value=null) {
		
	}
	
	public static function get($group=null,$key=null) {
		
	}
	
}	

?>