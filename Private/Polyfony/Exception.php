<?php

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
	
	public static function convertTraceToHtml(
		array $trace
	) :string {

		$stack = '<div class="card-body">';

		foreach($trace as $index => $call) {
			// in case the stack element is not from an object
			$class 	= isset($call['class']) ? $call['class'] : '';
			$type 	= isset($call['type']) ? $call['type'] : '';
			// shorten the file path to lower cognitive load
			$file 	= str_replace('/var/www/', '', $call['file']); 
			// format a clean call
			$stack .= 
			"<span class=\"lead\">{$index} {$class}<strong>{$type}{$call['function']}</strong></span> ";
			$stack .= 
			"<span class=\"badge bg-warning text-black\">@line {$call['line']}</span> in <span class=\"text-secondary\">{$file}</span><br />";
		}

		return "$stack</div>";

	}
	
	private function __toHtml() {
		
		// return the formatted trace
		return(self::convertTraceToHtml($this->getTrace()));
		
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
