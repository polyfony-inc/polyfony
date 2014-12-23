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

class Locales {
	
	protected static $_locales = null;
	protected static $_language = null
	
	public static function init() {
		
		// if the language is not set already we detect it
		self::$_language !== null ?: self::detect();
		
		// load from the cache if available
		Cache::has('Locales') and Config::isProd() ? self::$_locales = Cache::get('Locales') : self::load();
		
	}
	
	private static function detect() {
		
		// if accept language header is not set we use default
		!Request::headers('Accept-Language') ?: self::$_language = Config::get('locales','default');
			
		$auto_language = (Request::headers('Accept-Language') ? substr(Request::headers('Accept-Language'),0,2) : Config::get('locales','default');
			
	}
	
	private static function load($file) {
		
		// save the locales to the cache
		
	}
	
	public static function setLanguage() {
		
	}
	
	public static function getLanguage() {
		
	}
	
	public static function get($key,$language=null) {
	
		// if locales are not loaded yet
		$_locales !== null ?: self::init();
		
		// return the key in the right local or turn the key if the locale does not exist
		return(isset(self::$_locales[self::$_language]) ? self::$_locales[self::$_language] : $key);
		
	}
	
	public static function set($key,$value) {
		
	}
	
	
}

?>