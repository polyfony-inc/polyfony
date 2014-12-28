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
	protected static $_language = null;
	
	public static function init() {

		// if the language is not set already we detect it
		self::$_language !== null ?: self::detect();		
		// load from the cache if available
		(Cache::has('Locales') && Config::isProd()) ? self::$_locales = Cache::get('Locales') : self::load();
		
	}
	
	private static function detect() {
		
		// if accept language header is not set
		self::$_language = Request::header('Accept-Language') ? 
			// then we use it
			substr(Request::header('Accept-Language'),0,2) : 
			// else we use the default language
			Config::get('locales','default');

	}
	
	private static function load() {
		
		// load locales
		self::$_locales = array();
		// for each locale file available
		foreach(Bundles::getAvailable() as $bundle) {
			// for each locale in that bundle
			foreach(Bundles::getLocales($bundle) as $locale_file) {
				// import it
				self::import($locale_file);
			}
		}
		// save the freshly loaded locales to the cache
		Cache::put('Locales',self::$_locales,true);
		
	}
	
	private static function import($file) {
		// import the locales from provided file
		$lines	= explode("\n",file_get_contents($file));
		// use the first line as an index of languages codes
		$index	= explode("\t",$lines[0]);
		// for each language declared in the first line
		foreach($index as $position => $language) { 
			// trim and save it
			$index[$position] = trim($language,'"\''); 
		}
		// remove the first indexes line
		unset($lines[0]);
		// for each line of translations
		foreach($lines as $line) {
			// get each available translation
			$tabs = explode("\t",$line);
			// get the key for that translation
			$keyword = trim($tabs[0],'"\'');
			// remove the key
			unset($tabs[0]);
			// for each language of that string
			foreach($tabs as $position => $locale) {
				// language
				$language = $index[$position];
				// push this locale
				self::$_locales[$language][$keyword] = trim($locale,'"\'');
			}
		}
	}
	
	public static function setLanguage($language) {
		
		// set language if available, or fallback to default
		self::$_language = in_array($language,Config::get('locales','available')) ? $language : Config::get('locales','default');
		
	}
	
	public static function getLanguage() {
		
		// if not set, we detect
		self::$_language ?: self::detect();
		
		// return the language
		return(self::$_language); 
		
	}
	
	public static function get($key,$language=null) {
	
		// if locales are not loaded yet
		self::$_locales !== null ?: self::init();
		
		// if forced language is set
		$language = $language !== null ? $language : self::$_language;
		
		// return the key in the right local or turn the key if the locale does not exist
		return(isset(self::$_locales[$language][$key]) ? self::$_locales[$language][$key] : $key);
		
	}
	
	public static function set($key,$value) {
		
	}
	
	
}

?>
