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
		
		// load the main configuration
		self::$_config = array_merge(
			parse_ini_file("../Private/Config/Config.ini"),
			parse_ini_file("../Private/Config/{self::$_environment}.ini")
		);
		
	}
	
	private static function detectFromCLI() {
	
		// use the first command line argument as environment name
		self::$_environment = $_ARGV[1] == 'Dev' ? 'Dev' : 'Prod';
		
	}
	
	private static function detectFromHTTP() {
		
		// if we are running on the development port
		self::$_environment = pfRequest::server('SERVER_PORT') == pfConfig::get('request','dev_port') ? 'Dev' : 'Prod';

	}
	
	public static function set($group,$key,$value=null) {
		
		// set the proper value
		self::$_config[$group][$key] = $value;
		
	}
	
	public static function get($group,$key=null) {
		
		// return the proper config
		return($key ? self::$_config[$group][$key] : self::$_config[$group]);
		
	}
	
}	

?>