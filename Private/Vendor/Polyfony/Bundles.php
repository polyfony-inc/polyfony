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
 
class Bundles {

	protected static $_bundles		= array();
	protected static $_routes		= array();
	protected static $_runtimes		= array();

	// will get the list of bundles and get their routes and runtimes
	public static function init() {

		// if cache is enabled and in prod load the cache, else parse bundles
		(Cache::has('Includes') and Config::isProd()) ? self::loadCachedDependencies() : self::loadDependencies();
		
		// include what has been found
		self::includeLoaders();
		
	}
	
	private static function loadCachedDependencies() {
	
		// get from the cache
		$cache = Cache::get('Includes');
		// put everything at its rightful place
		self::$_bundles		= $cache['bundles'];
		self::$_routes		= $cache['routes'];
		self::$_runtimes	= $cache['runtimes'];
		
	}
	
	private static function loadDependencies() {
	
		// for each available bundle
		foreach(scandir('../Private/Bundles/') as $bundle) {
			// if it's an actual file
			if(Filesystem::isNormalName($bundle)) {
				// remember the bundle name
				self::$_bundles[] = $bundle;
				// route file
				$bundle_routes = "../Private/Bundles/{$bundle}/Loader/Route.php";
				// runtime file
				$bundle_runtime = "../Private/Bundles/{$bundle}/Loader/Runtime.php";
				// if a route file exists
				file_exists($bundle_routes) ? self::$_routes[] = $bundle_routes : '';
				// if a runtime file exists
				file_exists($bundle_runtime) ? self::$_runtimes[] = $bundle_runtime : '';
			}
		}
		// save in the cache (overwrite)
		Cache::put('Includes', array(
			'routes'	=>self::$_routes,
			'runtimes'	=>self::$_runtimes,
			'bundles'	=>self::$_bundles
		), true);
		
	}
	
	private static function includeLoaders() {
		
		// for each route or runtime filavailablee
		foreach(array_merge(self::$_routes, self::$_runtimes) as $file) {
			// include it
			include($file);	
		}
		
	}
	
	// get assets for a bundle
	public static function getAssets($bundle) {
		
		$assets_types = array();
		
		foreach(scandir("../Private/Bundles/{$bundle}/Assets/") as $asset_type) {
			
			if(is_dir("../Private/Bundles/{$bundle}/Assets/{$asset_type}")) {
			
			//	$assets_types[] = "../Private/Bundles/{$bundle}/Assets/{$asset_type}/";
			
			}
			
		}
		
		return($assets_types);
		
	}
	
	// get locales for a bundle
	public static function getLocales($bundle) {

		// declare an array to hold the list
		$locales = array();
		// set the locales path
		$locales_path = "../Private/Bundles/{$bundle}/Locales/";
		// if the directory exists
		if(Filesystem::exists($locales_path) && Filesystem::isDirectory($locales_path)) {
			// for each file in the directory
			foreach(scandir($locales_path) as $locales_file) {
				// if the file is a normal one
				if(Filesystem::isNormalName($locales_file)) {
					// push it into the array of locales
					$locales[] = $locales_path . $locales_file;
				}
			}
		}
		// return all found locales
		return($locales);
		
	}
	
	// get the list of available bundles
	public static function getAvailable() {
		
		// return the current list
		return(self::$_bundles);
		
	}
	
}

?>
