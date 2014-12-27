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
	public function __construct($name) {
		$this->name			= $name;
		$this->trigger		= null;
		$this->bundle		= null;
		$this->controller	= null;
		$this->action		= null;
		$this->restrictions	= array();
	}
	
	// set the url to match
	public function url($url) {
		$this->url = $url;
		return $this;
	}

	// set an associative array of contraints for the url parameters
	public function restrict($restrictions) {
		$this->restrictions = $restrictions;
		return $this;
	}
	
	// set the name of a parameter that will trigger the action
	public function trigger($trigger) {
		$this->trigger = $trigger;
		return $this;
	}

	// set the destination for that route
	public function destination($bundle, $controller=null, $action=null) {
		$this->bundle = $bundle;
		$this->controller = $controller != null ? $controller : 'Index';
		$this->action = $action != null ? $action : null;
		return $this;
	}
}