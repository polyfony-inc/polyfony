<?php
/**
 * PHP Version 5
 * Exceptions that are not catched will be collected here 
 * and forwarded to the exception route if it exists
 * an error code will be set in the response on the fly.
 * All that is left is to set an eventual redirect, 
 * redirectAfter of different output type for ajax in the controller
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
			Store\Request::put('exception', $exception, true);
			// set the proper header in the response
			Response::setStatus($exception->getCode() != 0 ? $exception->getCode() : 500);
			// dispatch to the exception controller
			Dispatcher::forward(Router::getRoute('exception'));
			// render the response
			Response::render();
		}
		
	}
	
	public function getCleanStack() {
		
		// a better looking exception stack will be created here until
		
	}
	
}

?>