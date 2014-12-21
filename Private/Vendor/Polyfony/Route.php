<?php
namespace Polyfony;

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
class Route
{

	// url to match
	public $url;
	// parameter restriction
	private $parameters;
	// name of that route
	private $name;
	// bundle destination
	private $bundle;
	// controller destination
	private $controller;
	// action destination
	private $action;
	// (optional) url variable to trigger action
	private $trigger;


	public function __construct($name) {
		$this->name = $name;
	}

	public function url($url) {
		$this->url = $url;
		return $this;
	}

	public function restrict($parameters) {
		$this->parameters = $parameters;
		return $this;
	}
	
	public function trigger($trigger) {
		$this->trigger = $trigger;
		return $this;
	}

	public function destination($bundle, $controller=null, $action=null) {
		$this->bundle = $bundle;
		$this->controller = $controller != null ? $controller : 'Index';
		$this->action = $action != null ? $action : null;
		return $this;
	}
}