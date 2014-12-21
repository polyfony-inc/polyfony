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

	public function __construct($message=null, $code=0, Exception $previous=null) {

		// if the router has an error route registered
		if(Router::hasRoute('error')) {
			// store the exception so that the error controller can format it later on
			Polyfony\Store\Request::put('exception',array(
				'message'	=>$message,
				'code'		=>$code,
				'previous'	=>$previous
			),true);
			// dispatch to the error controller
			Dispatcher::forward(Router::getRoute('error'));
		}
		// use the native exception
		else {
			// call the parent exception
			die('Fuck, something screwed up.');
		}			
			
	}
	
}

?>