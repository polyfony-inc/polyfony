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

		// set handler for all non catched PHP errors if Config::get('core','use_strict')	
		!Config::get('config','use_strict') ?: set_error_handler('Polyfony\Exception::catchErrorException');
		
	}
	
	// this will convert a PHP error into an exception, even for a notice or warning
	public static function catchErrorException($severity, $message, $filename, $lineno) {
		
		// build message
		$message = "$message at line $lineno in $filename";
		// throw a new exception in case of notice, warning, anything !
		throw new Exception($message , 500); 
		
	}
	
	public static function catchException($exception) {
	
		// if the router has an exception route
		if(Router::hasRoute('exception')) {
			// change the status code of the response
			Response::setStatus($exception->getCode() ? $exception->getCode() : 500);
			// store the exception so that the error controller can format it later on
			Store\Request::put('exception', $exception, true);
			// dispatch to the exception route
			Router::forward(Router::getRoute('exception'));
			// render the response
			Response::render();
		}
		// no exception handler or output is not html, throw normal exception
		else {
			// hard set the error code
			header(Request::server('SERVER_PROTOCOL') . ' 500 Internal Server Error', true, 500);
			// hard set cache restriction
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			// throw exception normally
			Throw $exception;
		}
		
	}
	
	private function __toHtml() {
		
		// container
		$this->cleanStack = '<pre class="xdebug-var-dump">';
		// for each call
		foreach($this->getTrace() as $index => $call) {
			// in case the stack element is not from an object
			$call['class'] = isset($call['class']) ? $call['class'] : '';
			$call['type'] = isset($call['type']) ? $call['type'] : '';
			// format a clean call
			$this->cleanStack .= "{$index} <b>{$call['class']}</b>{$call['type']}{$call['function']} ";
			$this->cleanStack .= "<span class=\"label label-default\">@line {$call['line']}</span> in <small>{$call['file']}</small><br />";
		}
		// end of container
		$this->cleanStack .= '</pre>';
		// return the formatted exeption
		return($this->cleanStack);
		
	}
	
	private function __toText() {
	
		// raw text format
		return($this->getTraceAsString());
		
	}
	
	// a better looking exception stack based on bootstrap/xdebug support
	public function __toString() {
		
		// if the output type different from html
		return(in_array(Response::getType(), array('html','html-page')) ? 
			$this->__toHtml() : $this->__toText());
		
	}
	
}

?>
