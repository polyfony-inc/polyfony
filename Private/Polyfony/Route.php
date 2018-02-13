<?php
/**
 * A single route for the application.
 *
 * Routes can contain variables which are prepended by a colon. Paths are greedy
 * by default, they will grab any URL that they match irrespective of what comes
 * after the matched fragments of the request URL. Anything after the route path
 * will be parsed as a GET variable.
 * @copyright Copyright (c) 2012-2013 Christopher Hill
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author    Christopher Hill <cjhill@gmail.com>
 * @package   MVC
 */
 
namespace Polyfony;

class Route {

	// url to match
	public $url;
	// name of that route
	public $name;
	// bundle destination
	public $bundle;
	// controller destination
	public $controller;
	// (optional) action destination
	public $action;
	// (optional) url variable to trigger action
	public $trigger;
	// (optional)method to match 
	public $method;
	// (optional) parameter restriction
	public $restrictions;
	// (optional) redirection url
	public $redirect;
	// (optional) redirection status code
	public $redirectStatus;

	// construct the route given its name
	public function __construct(string $name = null) {
		$this->name				= $name ?: uniqid('route-');
		$this->trigger			= null;
		$this->bundle			= null;
		$this->controller		= null;
		$this->action			= null;
		$this->method 			= null;
		$this->redirect 		= null;
		$this->redirectStatus 	= null;
		$this->restrictions		= [];
	}
	
	// set the url to match
	public function url(string $url = null) :self {
		$this->url = $url;
		return $this;
	}

	public function method(string $method = null) :self {
		$this->method = in_array(strtolower($method), Request::METHODS) ? strtolower($method) : null;
		return $this;
	}

	// shortcut for method
	public function get() :self {
		return $this->method('get');
	}

	// shortcut for method
	public function post() :self {
		return $this->method('post');
	}

	// shortcut for method
	public function delete() :self {
		return $this->method('delete');
	}

	// shortcut for method
	public function put() :self {
		return $this->method('put');
	}

	// set an associative array of contraints for the url parameters
	public function where(array $restrictions) :self {
		$this->restrictions = $restrictions;
		return $this;
	}

	// set an associative array of constraints (alias)
	public function restrict(array $restrictions) :self {
		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Route->restrict() is deprecated, use Route->where() instead', 
			E_USER_DEPRECATED
		);
		return $this->where($restrictions);
	}
	
	// set the name of a parameter that will trigger the action
	public function trigger(string $trigger) :self {
		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Route->destination() is deprecated, use Router::map("url","Bundle/Controller@{your_trigger}") instead', 
			E_USER_DEPRECATED
		);
		$this->trigger = $trigger;
		return $this;
	}

	// set the destination for that route
	public function destination(string $bundle, string $controller=null, string $action=null) :self {
		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Route->destination() is deprecated, use Router::map("url","Bundle/Controller@action") instead', 
			E_USER_DEPRECATED
		);
		$this->bundle = $bundle;
		$this->controller = $controller !== null ? $controller : 'Index';
		$this->action = $action !== null ? $action : null;
		return $this;
	}

	// shortcut for destination
	public function to($merged_destination) :self {
		// explode the parameters
		list($this->bundle, $controller_with_action) = explode('/', $merged_destination);
		// if the parameters are incomplete
		if(!$controller_with_action) {
			// we don't allow to proceed
			Throw new Exception('Route->to() should look like ("Bundle/Controller@method"');
		}
		// explode parameters further
		list($this->controller, $action) = explode('@', $controller_with_action);
		// if the action exists and contain a semicolon or is surounded by braquets
		if($action && (substr($action, 0, 1) == ':' || (substr($action, 0, 1) == '{') && substr($action, -1, 1) == '}')) {
			// we have an action triggered by an url parameter, remove special chars, and set the trigger
			$this->trigger = str_replace([':','{','}'],'', $action);
			// action is not known yet
			$this->action = null;
		}
		// the action exist, and it is a regual one
		elseif($action) {
			// define the action
			$this->action = $action;
		}
		// the action is totaly unspecified
		else {
			// so we use the index
			$this->action = 'index';
		}
		// return the route
		return $this;
	}
}
