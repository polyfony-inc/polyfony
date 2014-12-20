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

class Controller {


	// method to override
	public function preAction() {
		
	}
	
	// method to override
	public function postAction() {
		
	}

	// method to override
	public function defaultAction() {
		
	}
	
	// method to override
	public function indexAction() {
		
	}
	
	// forward to another controller in the same bundle
	public function forward($controller, $action=null) {
		
		// call the pfRouter ? or fork self to preserve some context from the preAction ?
		
	}
	
	// build an url (or a link) ? given a route name and its parameters
	public function link($route, $parameters=null) {
		
	}
	
	// alias
	public function isGranted($module, $level=null) {
		
		// ask the pfsecurity for that module and bypass level
		return(Security::hasModule($module,$level));
		
	}
	
}	

?>