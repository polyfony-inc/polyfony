<?php

namespace Polyfony;

class Locales {
	
	protected static $_locales = null;
	protected static $_language = null;
	
	protected static $_load_time = 0;

	public static function init() {

		// if the language is not set already we detect it
		self::$_language !== null ?: self::detect();		
		// load from the cache if available
		Config::isProd() && Cache::has('Locales') ? self::$_locales = Cache::get('Locales') : self::load();
		
	}
	
	public static function getStatistics() :array {
		return [
			'locales_count'	=>self::$_locales ? count(self::$_locales[Config::get('locales','default')]) : 0,
			'load_time'		=>self::$_load_time
		];
	}

	private static function detect() {
		
		// if the store has a language cookie and that language is valid
		if(
			// if the store has a language cookie
			Store\Cookie::has(Config::get('locales','cookie')) && 
			// if that language is authorized
			in_array(
				Store\Cookie::get(Config::get('locales','cookie')), 
				Config::get('locales', 'available')
			)
		) {
			// use that language
			self::$_language = Store\Cookie::get(Config::get('locales','cookie'));
		}

		// if we didn't find the correct language yet but we have something to work with in the headers
		if(self::$_language === null && Request::header('Accept-Language')) {

			// if the browser language is acceptable
			$browser_language = substr(Request::header('Accept-Language'), 0, 2);

			// use it or use default instead
			self::$_language = in_array($browser_language, Config::get('locales', 'available')) ? 
				$browser_language :
				Config::get('locales', 'default');

		}
		// we cannot find anything useful in the headers
		elseif(self::$_language === null) {

			// use the default language
			self::$_language = Config::get('locales', 'default');

		}

	}
	
	private static function load() {
		
		// only if profiler enabled
		if(Config::get('profiler', 'enable')) {
			$start = microtime(true);
		}
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
		// only if profiler enabled
		if(Config::get('profiler', 'enable')) {
			self::$_load_time = microtime(true) - $start;
		}
		
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
		// memorize the language for a month
		Store\Cookie::put(Config::get('locales','cookie'), self::$_language, true, 24 * 30);
		
	}
	
	public static function getLanguage() {
		
		// if not set, we detect
		self::$_language ?: self::detect();
		
		// return the language
		return(self::$_language); 
		
	}
	
	public static function get($key, $language=null, ?array $variables=[]) {
	
		// if locales are not loaded yet
		self::$_locales !== null ?: self::init();
		// if forced language is set
		$language = $language !== null ? $language : self::$_language;
		// return the key in the right local or turn the key if the locale does not exist
		$localized_string = isset(self::$_locales[$language][$key]) ? 
			self::$_locales[$language][$key] : $key;
		// replace each variables
		foreach($variables as $variable_key => $variable_value) {
			$localized_string = str_replace(
				$variable_key, 
				self::get($variable_value, $language), 
				$localized_string
			);
		}
		// returned the consolidated locale 
		return $localized_string;

	}
	
}

?>
