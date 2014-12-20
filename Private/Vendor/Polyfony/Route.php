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

	protected $name;
	protected $bundle;
	protected $controller;
	protected $action;
	protected $parameters;
	protected $trigger;

class Route {
	
	// set the name of the route (for reverse routing purpose)
	public function setName($name) {
		
	}
	
	// set the full url
	public function setUrl($url) {
		
	}
	
	// set constraints for the parameters
	public function setParameters($parameters=array()) {
		
	}
	
	// set the destination for that route
	public function setDestination($bundle, $controller, $action=null) {
		
	}
	
	// define a parameter than will define the action to call
	public function setTrigger($parameter) {
		
	}
	
	// define the method or methods to match
	public function setMethod($method) {
		
	}
	
	// save this route in the router
	public function save() {
		
		// save the route
		Router::setRoute($this);
		
	}
	
}

?>