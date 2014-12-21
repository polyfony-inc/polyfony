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

class Dispatcher {
	
	protected static $_controller;
	
	public static function forward($route) {
		
		// set full controller
		$script = "../Private/Bundles/{$route->bundle}/Controllers/{$route->controller}.php";
		// set the full class
		$class = "{$route->controller}Controller";
		// set full method
		$method = "{$route->action}Action";
		
		// if script is missing and it's not the error script
		if(!file_exists($script)) {
			// new polyfony exception
			Throw new Exception("Dispatcher::forward() : Missing controller file [{$script}]",500);	
		}
		// include the controller's file
		require($script);
		// if class is missing from the controller and not in error route
		if(!class_exists($class,false)) {
			// new polyfony exception
			Throw new Exception("Dispatcher::forward() : Missing controller class [{$class}] in [{$script}]",500);	
		}
		
		// instanciate
		self::$_controller = new $class;
		// if method is missing replace by default
		$method = method_exists($class,$method) ? $method : 'defaultAction';
		// pre action
		self::$_controller->preAction();
		// marker
		Profiler::setMarker('preaction');
		// call the method
		self::$_controller->$method();
		// marker
		Profiler::setMarker('controller');
		// post action
		self::$_controller->postAction();	
		// marker
		Profiler::setMarker('postaction');
		
	}
	
}	

?>