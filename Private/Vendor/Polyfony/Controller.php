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

class Controller {


	// method to override
	public function preAction() {
		
	}
	
	// method to override
	public function postAction() {
		
	}

	// method to override
	public function indexAction() {
		
	}

	// method to override
	public function defaultAction() {
		// default will throw an exception
		throw new Exception('This action does not exist', 500);
	}
	
	// include a view
	final public function view($view_name) {
		
		// build the path for that view
		$view_path = "../Private/Bundles/" . Router::getCurrentRoute()->bundle ."/Views/{$view_name}.php";
		// if the file does not exist
		if(!file_exists($view_path)) {
			// throw an exception
			Throw new Exception("Controller->view() View file does not exist [{$view_path}]", 500);
		}
		// the file exists
		else {
			// simply include it
			require($view_path);	
		}
		
	}
	
	// forward to another controller in the same bundle
	final public function forward($controller, $action=null) {
		
		// add a profiler marker
		Profiler::setMarker('forward');

		// get the current route as a base
		$route = Router::getCurrentRoute();
		
		// and alter it
		$route->controller	= $controller;
		$route->action		= $action;
		
		// forward to the new route
		Dispatcher::forward($route);
		
	}
	
	// alias to router / build an url given a route name and its parameters
	final public function url($route, $parameters=array()) {
		
		// return the reversed route as an url
		return(Router::reverse($route, $parameters));
		
	}

	final public function link() {
		
	}
	
	// get an empty query
	final public function query() {

		// return a new query
		return(Database::query());
		
	}
	
	// alias to security / check if requirements are met
	final public function isGranted($module, $level=null) {
		
		// ask the pfsecurity for that module and bypass level
		return(Security::hasModule($module, $level));
		
	}
	
}	

?>
