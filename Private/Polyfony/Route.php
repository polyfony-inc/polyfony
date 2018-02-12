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
	// method to match
	public $method;
	// parameter restriction
	public $restrictions;
	// name of that route
	public $name;
	// bundle destination
	public $bundle;
	// controller destination
	public $controller;
	// action destination
	public $action;
	// (optional) url variable to trigger action
	public $trigger;

	// construct the route given its name
	public function __construct(string $name) {
		$this->name			= $name;
		$this->trigger		= null;
		$this->bundle		= null;
		$this->controller	= null;
		$this->action		= null;
		$this->method 		= null;
		$this->restrictions	= array();
	}
	
	// set the url to match
	public function url(string $url) :self {
		$this->url = $url;
		return $this;
	}

	public function method(string $method) :self {
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
	public function restrict(array $restrictions) :self {
		$this->restrictions = $restrictions;
		return $this;
	}
	
	// set the name of a parameter that will trigger the action
	public function trigger(string $trigger) :self {
		$this->trigger = $trigger;
		return $this;
	}

	// set the destination for that route
	public function destination(string $bundle, string $controller=null, string $action=null) :self {
		$this->bundle = $bundle;
		$this->controller = $controller !== null ? $controller : 'Index';
		$this->action = $action !== null ? $action : null;
		return $this;
	}

	// shortcut for destination
	public function to($merged_destination) {
		// explode the parameters
		list($bundle, $controller_with_action) = explode('/', $merged_destination);
		// if the parameters are incomplete
		if(!$controller_with_action) {
			Throw new Exception('Route->to() should look like ("Bundle/Controller->method"');
		}
		// explode parameters further
		list($controller, $action) = explode('->', $controller_with_action);
		// define the action
		// if the action contains :
		// if empty
		// if normal...

	}
}
