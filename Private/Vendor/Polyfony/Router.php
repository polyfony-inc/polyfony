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
	
	// check if the route exists
	public static function hasRoute($route_name) {
	
		// true if route exists, false otherwise
		return(isset(self::$_routes[$route_name]) ? true : false);
		
	}
	
	// get a specific route
	public static function getRoute($route_name) {
		
		// return the route of false
		return(isset(self::$_routes[$route_name]) ? self::$_routes[$route_name] : false);
		
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
		// marker
		Profiler::setMarker('init_router');
		// if no match is found and we don't have an error route to fallback on
		if(!self::$_match and !isset(self::$_routes['exception'])) {
			// throw a native exception since there is no cleaner alternative
			Throw new \Exception('Router::route() no matching route and no error route either',404);
		}
		// else we can use the error route
		elseif(!self::$_match and isset(self::$_routes['exception'])) {
			// use the error handler
			Throw new Exception('Router::route() no matching route',404);
		}
		// send the matching route to the dispatcher
		Dispatcher::forward(self::$_match);	
		
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
		// if the route is declared to handle some parameters
		if(is_array($route_portions)) {			
			// remove the base portion from the request url
			$request_portions = str_replace($route_base,'',$request_url);
			// if the remaining request url has parameters, explode them or return an empty array
			$request_parameters = strstr($request_portions,'/') ? explode('/',$request_portions) : array();
			// remove the static portion of the route url
			unset($route_portions[0]);
			// for clarity rename the route portions to routes parameters
			$route_parameters = array_values($route_portions);
			// for each parameter that the route can handle
			foreach($route_parameters as $index => $parameter_name) {
				// clean the parameter name
				$parameter_name = trim($parameter_name,'/');
				// check if it is provided in the request url
				!isset($request_parameters[$index]) ?: Request::setUrlParameter($parameter_name,$request_parameters[$index]);
			}
			// for each restriction of the route, check if the parameters are matching else return false
			foreach($route->restrictions as $parameter => $restriction) {
				// if said parameter is set
				if(Request::get($parameter)) {
					// if the restriction is an array of values
					if(is_array($restriction) and in_array(Request::get($parameter),$restriction)) {
						// route matches
						continue;
					}
					// if the restriction is a regex is has to match
					elseif(preg_match('/^{$restriction}$/iu',Request::get($parameter))) {
						// route matches
						continue;	
					}
					// if the restriction is only to be set
					elseif($restriction === true) {
						// route matches
						continue;
					}
					// restriction is not met
					else {
						// route does not match
						return(false);	
					}
				}
				// parameter is not set
				else {
					// if it should be
					if($restriction === true) {
						// route does not match
						return(false);	
					}
				}
			}
			// if a trigger is defined use to to set the action
			if($route->trigger !== null) {
				// update dynamically the route's action or fallback to index if missing
				$route->action = Request::get($route->trigger) ? Request::get($route->trigger) : 'index';
			}
		}
		else {
			// no parameters to treat (static route), check if perfect match
			if($request_url != $route->url) {
				// not perfectly matching
				return(false);	
			}
		}
		// we got there ? then we've find out route !
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
		$url = self::$_routes[$route_name]->url;

		// And replace the variables in the
		foreach ($parameters as $variable => $value) {
			$url = str_replace(":{$variable}", urlencode($value), $url);
		}

		return ($url);
	}
	
}


?>