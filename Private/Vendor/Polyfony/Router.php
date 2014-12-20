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
	protected static $_bundle;
	protected static $_controller;
	protected static $_action;
	
	// map a route
	public static function setRoute($name,$url,$restrict,$destination) {
		
	}
	
	// find the proper route
	public static function route() {
		
		// hard set controller/action for development purpose
		require('../Private/Bundles/Example/Controllers/Example.php');

		self::$_controller = new \ExampleController();
		self::$_controller->indexAction();
		
	}
	
	// dispatch to the proper controller
	public static function dispatch($bundle, $controller, $action=null) {
		
	}
	
}


?>