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
		
	}
	
	private static function loadCachedDependencies() {
	
		// get from the cache
		$cache = Cache::get('Includes');
		// put everything at its rightful place
		self::$_bundles = $cache['bundles'];
		
	}
	
	private static function loadDependencies() {
	
		// declare includes to return
		$includes = array();
		// for each available bundle
		foreach(scandir('../Private/Bundles/') as $bundle) {
			// if it's an actual file
			if(Tools::isFile($bundle)) {
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
		
		foreach(array_merge(self::$_routes, self::$_runtimes) as $file) {
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
		
		$locales = array();
		
		foreach(scandir("../Private/Bundles/{$bundle}/Locales/") as $locales) {
			
			if(self::isFile($locales)) {
			
			//	$locales[] = "../Private/Bundles/{$bundle}/Locales/{$locales}";
			
			}
			
		}
		
		return($locales);
		
	}
	
	// get the list of bundles
	public static function getBundles() {
		
		// return the current list
		return(self::$_bundles);
		
	}
	
	// private
	private static function isFile() {
		
	}
	
}

?>