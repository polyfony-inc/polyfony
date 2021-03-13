<?php

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

	private static function canWeUseCachedRoutes() :bool {
		return Config::isProd() && Cache::has('Routes') && Config::get('router', 'cache');
	}
	
	private static function restoreCachedRoutes() :void {
		// restore the routes from the cache
		foreach(Cache::get('Routes') as $route_name => $route) {
			// reinstanciate a route
			$route_object = new Route($route_name);
			// for each attribute
			foreach($route as $key => $value) {
				// re-set it
				$route_object->{$key} = $value;
			}
			// add the route
			self::$_routes[$route_name] = $route_object;
		}
	}

	public static function includeBundlesRoutes($bundles_routes_files) :void {
		
		// if we are in prod, and allowed to cache routes, and have a cache file available
		if(self::canWeUseCachedRoutes()) {
			// we can restore the routes from the cache
			self::restoreCachedRoutes();
		}
		// then we have to include the routes files
		else {
			// for each of those files
			foreach($bundles_routes_files as $file) {
				// include it
				include($file);
			}
			// as we have build the routes objet, we should cache them
			Cache::put('Routes', self::$_routes, true);
		}

	}
	
	// new syntax for mapping routes
	public static function map(
		string $url = null, string $destination, string $route_name = null, string $method = null
	) :Route {
		// We cannot allow duplicate route names for reversing reasons
		if($route_name && self::hasRoute($route_name)) {
			// throw an exception
			throw new Exception("Router::map() The route {$route_name} has already been declared");
		}
		// create a new route
		$route = new Route($route_name);
		// configure the route
		$route
			->setUrl($url)
			->setDestination($destination)
			->setMethod($method);
		// add it to the list of known routes
		self::$_routes[$route->name] = $route;
		// return the route for finer tuning
		return self::$_routes[$route->name];
	}

	// new syntax for mapping routes
	public static function get(string $url, string $destination, $route_name = null) :Route {
		return self::map($url, $destination, $route_name, 'get');
	}

	// new syntax for mapping routes
	public static function post(string $url, string $destination, $route_name = null) :Route {	
		return self::map($url, $destination, $route_name, 'post');
	}

	// new syntax for mapping routes
	public static function delete(string $url, string $destination, $route_name = null) :Route {	
		return self::map($url, $destination, $route_name, 'delete');
	}

	// new syntax for mapping routes
	public static function put(string $url, string $destination, $route_name = null) :Route {	
		return self::map($url, $destination, $route_name, 'put');
	}

	// new syntax for quick redirects
	public static function redirect(string $source_url, string $redirection_url, int $status_code=301)  {
		// create a new route
		$route = new Route();
		// add it to the list of known routes
		self::$_routes[$route->name] = $route
			->setUrl($source_url)
			->setRedirect($redirection_url, $status_code);
		// return the route for finer tuning
		return self::$_routes[$route->name];
	}

	// check if the route exists
	public static function hasRoute(string $route_name) :bool {
		// true if route exists, false otherwise
		return isset(self::$_routes[$route_name]);
	}
	
	// get a specific route
	public static function getRoute(string $route_name) {
		// return the route of false
		return self::hasRoute($route_name) ? self::$_routes[$route_name] : false;
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

		// marker
		Profiler::setMarker('Router.route', 'framework');
		// get the requested url
		$request_url = Request::getUrl();
		// get the requested method
		$request_method = Request::getMethod();
		// Loop over each route and test to see if they are valid
		foreach(self::$_routes as $route) {
			// if the route matches
			if(self::routeMatch($request_url, $request_method, $route)) {
				// get it
				$matching_route = $route;
				// stop trying
				break;
			}
		}
		// marker
		Profiler::releaseMarker('Router.route');
		// if no match is found and we don't have an error route to fallback on
		if(!isset($matching_route)) {
			// throw a native exception since there is no cleaner alternative
			Throw new Exception('Router::route() no matching route', 404);
		}
		// send the matching route to the dispatcher
		self::forward($matching_route);	
		
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
		if(!$route->hasMethod($request_method)) {
			return false;
		}
		// if the url doesn't begin with the static segment or that route
		if(!$route->hasStaticUrlSegment($request_url)) {
			return false;
		}
		// if we've got a redirect, let's go for it
		$route->redirectIfItIsOne();
		// get a list of current request parameters, with numerical indexes
		$indexed_request_parameters = Request::getUrlIndexedParameters($route->staticSegment);
		// if restricttion against url parameters don't match
		if(!$route->validatesTheseParameters($indexed_request_parameters)) {
			return false;
		}
		// send the named parameters to the request class
		$route->sendNamedParametersToRequest($indexed_request_parameters);
		// deduce the dynamic action from the url parameters if necessary
		$route->deduceAction();
		// check if they match defined constraints
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
	public static function reverse(
		string $route_name, 
		array $parameters = [], 
		bool $is_absolute = false, 
		bool $force_tls = false
	) :string {
		// if the specified route doesn't exist
		if(!self::hasRoute($route_name)) {
			// we cannot reverse a route that does not exist
			throw new Exception("Router::reverse() : The [{$route_name}] route does not exist");
		}
		// return the reversed url
		return self::$_routes[$route_name]->getAssembledUrl($parameters, $is_absolute, $force_tls);
	}
	
	public static function forward(Route $route) :void {

		// get the full destination
		list($script, $class, $method) = $route->getDestination();
		// if script is missing from the bundle
		if(!file_exists($script)) {
			// new polyfony exception
			Throw new Exception(
				"Dispatcher::forward() : Controller file [{$script}] does not exist", 500);	
		}
		// include the controller's file
		require_once($script);
		// if the class is missing from the controller
		if(!class_exists($class,false)) {
			// new polyfony exception
			Throw new Exception(
				"Dispatcher::forward() : Controller class [{$class}] does not exist in [{$script}]", 500);	
		}
		// update the current route
		self::setCurrentRoute($route);
		// instanciate
		self::$_controller = new $class;
		// if the method does not exist in the controller
		if(!method_exists($class,$method)) {
			// new polyfony exception
			Throw new Exception(
				"Dispatcher::forward() : Method not implemented in [{$class}]", 501);	
		}
		// marker
		$id_pre_marker = Profiler::setMarker("{$route->controller}.preAction", "controller");
		// pre action
		self::$_controller->preAction();
		// marker
		Profiler::releaseMarker($id_pre_marker);
		// marker
		$id_marker = Profiler::setMarker("{$route->controller}.{$method}", "controller");
		// call the method
		self::$_controller->$method();
		// marker
		Profiler::releaseMarker($id_marker);
		// marker
		$id_post_marker = Profiler::setMarker("{$route->controller}.postAction", "controller");
		// post action
		self::$_controller->postAction();
		// marker
		Profiler::releaseMarker($id_post_marker);
		
	}
	
}


?>
