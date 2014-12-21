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

class Exception extends \Exception {
	
	
	public static function init() {
	
		// set the handler for all non-catched exceptions
		set_exception_handler('Polyfony\Exception::catchException');
		
	}
	
	public static function catchException($exception) {
	
		// if the router has an exception route we catch and route to it
		if(Router::hasRoute('exception')) {
			// store the exception so that the error controller can format it later on
			Store\Request::put('exception',$exception,true);
			// set the proper header in the response
			
			// dispatch to the error controller
			Dispatcher::forward(Router::getRoute('exception'));
		}
		
	}
	
}

?>