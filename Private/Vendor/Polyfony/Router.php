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

class Router {
	
	protected static $_routes;
	protected static $_match;
	
	public static function init() {
	
		// find a matching route
		self::route();
		
	}
	
	// map a route
	public static function addRoute($route_name) {
		
		// We cannot allow duplicate route names for reversing reasons
		if (isset(self::$_routes[$route_name])) {
			throw new \InvalidArgumentException("The route {$route_name} has already been declared.");
		}
		// create a new route
		self::$_routes[$route_name] = new Route($route_name);
		// return that route
		return self::$_routes[$route_name];
		
	}
	
	// find the proper route
	public static function route() {

		// get the requested url
		$request_url = Request::getUrl();
		// Loop over each route and test to see if they are valid
		foreach(self::$_routes as $route) {
			// if the route matches
			if(self::routeMatch($request_url, $route)) {
				// get it
				self::$_match = $route;
				// stop trying
				break;
			}
		}
	
		var_dump(self::$_routes,self::$_match);
		die();
	
		// if not found, use route named error404
		self::$_match != null ?: self::$_match = self::$_routes['error404'];
	
		// routes has matched, load the controller/action
		Dispatcher::loadController(
			self::$_bundles,
			self::$_controller,
			self::$_action
		);	
	}
	
	/**
	 * Test to see if this route is valid against the URL.
	 *
	 * @access private
	 * @param  array      $requestUrl The URL to test the route against.
	 * @param  Core\Route $route      A Route declared by the application.
	 * @return boolean
	 */
	private static function routeMatch($request_url, $route) {
		// break apart the route URL
		$route_portions = strstr($route->url,':') ? explode(':',$route->url) : $route->url;
		// set the base part
		$route_base = is_array($route_portions) ? $route_portions[0] : $route_portions;
		// if the start does not match
		if(stripos($request_url,$route_base) !== 0) {
			// not matching
			return(false);
		}
		// if we have parameters to treat
		if(is_array($route_portions)) {
			// get the request parameters
			$request_portions = explode('/',str_replace($route_base,'',$request_url));
			// remove the static portion
			unset($route_portions[0]);
			// for each parameter
			foreach($route_portions as $parameter_index => $parameter_name) {
				
				
				
			}
		}
		else {
			// no parameters to treat (static route), check if perfect match
			if($request_url != $route->url) {
				// not perfectly matching
				return(false);	
			}
		}
		
		
		return($route);
		

	}
	
	/**
	 * Reverse the router.
	 *
	 * Make a URL out of a route name and parameters, rather than parsing one.
	 * Note that this function does not care about URL paths!
	 *
	 * @access public
	 * @param  string    $route_name	The name of the route we wish to generate a URL for.
	 * @param  array     $parameters	The parameters that the route requires.
	 * @return string
	 * @throws \Exception           If the route does not exist.
	 * @static
	 */
	public static function reverse($route_name, $parameters = array()) {
		// Does the route actually exist?
		if (!isset(self::$_routes[$route_name])) {
			throw new Exception("The route {$route_name} does not exist.");
		}

		// Create a container for the URL
		$url = self::$_routes[$route_name]->route;

		// And replace the variables in the
		foreach ($parameters as $variable => $value) {
			$url = str_replace(":{$variable}", urlencode($value), $url);
		}

		return ($url);
	}
	
}


?>