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
	
	// all the routes
	protected static $_routes = [];
	// the currently matched route
	protected static $_match = null;
	// the currently instanciated controller
	protected static $_controller = null;
	
	public static function init() {

		// find a matching route
		self::route();
		
	}
	
	// LEGACY way of mapping a route
	public static function addRoute(string $route_name = null) :Route {
		
		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Router::addRoute() is deprecated, use Router::map() or Router::{get/post/put/delete}() instead', 
			E_USER_DEPRECATED
		);

		// We cannot allow duplicate route names for reversing reasons
		if($route_name && isset(self::$_routes[$route_name])) {
			// throw an exception
			throw new Exception("Router::addRoute() The route {$route_name} has already been declared");
		}
		// create a new route
		$route = new Route($route_name);
		// add the route to the list
		self::$_routes[$route->name] = $route;
		// return that route
		return self::$_routes[$route->name];
		
	}
	
	// NEW syntax for mapping routes
	public static function map(string $url = null, string $to, string $route_name = null, string $method = null) :Route {
		// We cannot allow duplicate route names for reversing reasons
		if($route_name && isset(self::$_routes[$route_name])) {
			// throw an exception
			throw new Exception("Router::map() The route {$route_name} has already been declared");
		}
		// create a new route
		$route = new Route($route_name);
		// configure the route
		$route
			->url($url)
			->to($to)
			->method($method);
		// add it to the list of known routes
		self::$_routes[$route->name] = $route;
		// return the route for finer tuning
		return self::$_routes[$route->name];
	}

	// new syntax for mapping routes
	public static function get(string $url, string $to, $route_name = null) :Route {
		return self::map($url, $to, $route_name, 'get');
	}

	// new syntax for mapping routes
	public static function post(string $url, string $to, $route_name = null) {	
		return self::map($url, $to, $route_name, 'post');
	}

	// new syntax for mapping routes
	public static function delete(string $url, string $to, $route_name = null) {	
		return self::map($url, $to, $route_name, 'delete');
	}

	// new syntax for mapping routes
	public static function put(string $url, string $to, $route_name = null) {	
		return self::map($url, $to, $route_name, 'put');
	}

	// new syntax for quick redirects
	public static function redirect(string $source_url, string $redirection_url, int $status_code = 301) :void {

	}

	// check if the route exists
	public static function hasRoute(string $route_name) :bool {
	
		// true if route exists, false otherwise
		return isset(self::$_routes[$route_name]);
		
	}
	
	// get a specific route
	public static function getRoute(string $route_name) {
		
		// return the route of false
		return isset(self::$_routes[$route_name]) ? self::$_routes[$route_name] : false;
		
	}
	
	// update the current route after forwarding
	public static function setCurrentRoute(Route $route) :void {
		
		// update the matched route
		self::$_match = $route;
		
	}
	
	// get the current route
	public static function getCurrentRoute() {
	
		// returned the matched route
		return self::$_match ? self::$_match : null;
		
	}
	
	// find the proper route
	public static function route() :void {

		// get the requested url
		$request_url = Request::getUrl();
		// get the requested method
		$request_method = Request::getMethod();
		// Loop over each route and test to see if they are valid
		foreach(self::$_routes as $route) {
			// if the route matches
			if(self::routeMatch($request_url, $request_method, $route)) {
				// get it
				self::$_match = $route;
				// stop trying
				break;
			}
		}
		// marker
		Profiler::setMarker('init_router');
		// if no match is found and we don't have an error route to fallback on
		if(!self::$_match) {
			// throw a native exception since there is no cleaner alternative
			Throw new Exception('Router::route() no matching route', 404);
		}
		// send the matching route to the dispatcher
		self::forward(self::$_match);	
		
	}
	
	/**
	 * Test to see if this route is valid against the URL.
	 *
	 * @access private
	 * @param  array      $requestUrl The URL to test the route against.
	 * @param  Core\Route $route      A Route declared by the application.
	 * @return boolean
	 */
	private static function routeMatch(string $request_url, string $request_method, Route $route) :bool {
		// if the method is set for that route, and it doesn't match
		if($route->method && $route->method != $request_method) {
			// don't even got any further
			return false;
		}
		// break apart the route URL
		$route_portions = strstr($route->url,':') ? explode(':',$route->url) : $route->url;
		
		// we should also be breaking appart the {} symbols (new syntax)
		// code to be produced here

		// set the base part
		$route_base = is_array($route_portions) ? $route_portions[0] : $route_portions;
		// if the start does not match
		if(stripos($request_url,$route_base) !== 0) {
			// not matching
			return false;
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
					if(is_array($restriction) && in_array(Request::get($parameter),$restriction)) {
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
						return false;	
					}
				}
				// parameter is not set
				else {
					// if it should be
					if($restriction === true) {
						// route does not match
						return false;	
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
				return false;	
			}
		}
		// we got there ? then we've find out route !
		return true;
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
	public static function reverse(string $route_name, array $parameters = array(), bool $absolute = false, bool $force_tls = false) :string {
		// Does the route actually exist?
		if(!isset(self::$_routes[$route_name])) {
			// we cannot reverse a route that does not exist
			throw new Exception("Router::reverse() The route [{$route_name}] does not exist");
		}
		// Create a container for the URL
		$url = self::$_routes[$route_name]->url;
		// for each provided parameter
		foreach($parameters as $variable => $value) {
			// replace it in the url
			$url = str_replace(":{$variable}/", urlencode($value) . '/', $url);
		}
		// the list of all parameters present in the url
		$all_parameters = (array) explode('/', $url);
		// for each parameter
		foreach($all_parameters as $index => $a_parameter) {
			// if it starts with a semicolon
			if(substr($a_parameter, 0, 1) == ':') {
				// if it's still in this form in the url, we remove it
				$url = str_replace("{$a_parameter}/", '', $url);
			}
		}
		// define the protocol to use (use the current one, or https if it is forced)
		$protocol = (($force_tls && Config::isProd()) or Request::getProtocol() == 'https')?
			'https' : 'http';
		// if we want an absolute url, prefix with the domain and with the port if the protocol is not https
		!$absolute ?: $url = $protocol . '://' . Config::get('router', 'domain') . 
		(Request::getPort() != 80 && $protocol == 'http' ? ':'.Request::getPort() : '') . $url;
		// return the reversed url
		return $url;
	}
	
	public static function forward(Route $route) {
		
		// set full controller
		$script = "../Private/Bundles/{$route->bundle}/Controllers/{$route->controller}.php";
		// set the full class
		$class = "{$route->controller}Controller";
		// set full method or index if none provided
		$method = $route->action ? "{$route->action}Action" : 'indexAction';
		// if script is missing and it's not the error script
		if(!file_exists($script)) {
			// new polyfony exception
			Throw new Exception("Dispatcher::forward() : Missing controller file [{$script}]", 500);	
		}
		// include the controller's file
		require_once($script);
		// if class is missing from the controller and not in error route
		if(!class_exists($class,false)) {
			// new polyfony exception
			Throw new Exception("Dispatcher::forward() : Missing controller class [{$class}] in [{$script}]", 500);	
		}
		// update the current route
		self::$_match = $route;
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