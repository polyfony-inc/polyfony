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

class Config {
	
	protected static $_environment;
	protected static $_config;
	
	public static function init() :void {
	
		// get all configurations available
		self::$_config = array(
			'Current'	=> parse_ini_file('../Private/Config/Config.ini', true),
			'Dev'		=> parse_ini_file('../Private/Config/Dev.ini', true),
			'Prod'		=> parse_ini_file('../Private/Config/Prod.ini', true)
		);
	
		// depending on the context, detect environment differently
		Request::isCli() ? self::detectFromCLI() : self::detectFromHTTP();
		
		// merge the base configuration with the environment specific one
		self::merge();

		// set the project root path
		self::$_config['config']['root_path'] = realpath(__DIR__.'/../../') . '/';

		// set the proper timezone
		!self::get('config', 'timezone') ?: date_default_timezone_set(self::get('config', 'timezone'));

	}
	
	public static function includeBundlesConfigs($bundles_configuration_files) :void {
		// for each of those files
		foreach($bundles_configuration_files as $file) {
			// include it
			include($file);
		}
	}

	private static function detectFromCLI() :void {
	
		// use the first command line argument as environment name
		self::$_environment = Request::argv(1) == 'Dev' ? 'Dev' : 'Prod';
		
	}
	
	private static function detectFromHTTP() :void {
		
		// if the detection method is the port
		if(self::$_config['Current']['config']['detection_method'] == 'port') {
			// if we are matching the development port
			self::$_environment = Request::server('SERVER_PORT') == self::$_config['Dev']['router']['port'] ? 
				'Dev' : 'Prod';
		}
		// else the detection method is the domain
		elseif(self::$_config['Current']['config']['detection_method'] == 'domain') {
			// if we are matching the development domain
			self::$_environment = (
				// if there seem to be an exotic port in the http host, use the server name instead
				stripos(Request::server('HTTP_HOST'),':') !== false ? 
					Request::server('SERVER_NAME') : 
					Request::server('HTTP_HOST')
				// detect environment using the most suitable domain variable available
				) == self::$_config['Dev']['router']['domain'] ? 
					'Dev' : 
					'Prod';
		}
		// the detection method is unknown
		else {
			// throw an exception
			Throw new \Exception('Config::detectFromHTTP() Environment detection method is unkown');
		}	

	}

	private static function merge() :void {

		// if in production a an aggregated cache is already available, load it
		if(Config::isProd() && Cache::has('Config')) {
			// load from the cache file
			self::$_config['Current'] = Cache::get('Config');
			// stop the function
			return;
		}
		// no cache available or enabled, we have to merge ourselves
		else {
			// for each configuration block that is specific
			foreach(self::$_config[self::$_environment] as $group => $group_config) {
				// merge the current configuration
				self::$_config['Current'][$group] = array_merge(
					self::$_config['Current'][$group],
					self::$_config[self::$_environment][$group]
				);
			}
			// if we are in production, cache the merged file
			!Config::isProd() ?: Cache::put('Config', self::$_config['Current'], true);
		}

	}
	
	public static function set(string $group, $key, $value=null) :void {
		// if only group + one parameters, set the whole group, else set a key of the group
		$value !== null ? self::$_config['Current'][$group][$key] = $value : self::$_config['Current'][$group] = $key;
	}
	
	public static function get(string $group, $key=null) {
		// return the proper config
		return($key ? self::$_config['Current'][$group][$key] : self::$_config['Current'][$group]);
	}

	public static function isDev() :bool {
		// return boolean
		return(self::$_environment == 'Dev');
	}
	
	public static function isProd() :bool {
		// return boolean
		return(self::$_environment == 'Prod');
	}
	
}	

?>
