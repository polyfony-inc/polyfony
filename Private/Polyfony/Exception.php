<?php

namespace Polyfony;

class Exception extends \Exception {
	
	
	public static function init() {
	
		// set the handler for all non-catched exceptions
		set_exception_handler('Polyfony\Exception::catchException');

		// set handler for all PHP errors	
		set_error_handler('Polyfony\Exception::catchError', E_ALL);
		
		// register a shutdown function to catch fatal errors that slipped thru
		register_shutdown_function('Polyfony\Exception::catchCatastrophicError');

	}

	// this will convert a PHP error into an exception, 
	// even for a notice or warning
	public static function catchError(
		int $severity, 
		string $message, 
		string $filename, 
		int $line_number
	) {

		// if we are not already on the exception route
		if((Router::getCurrentRoute()->name ?? '') != 'exception-handler') {
			// throw a new exception in case of notice, warning, anything !
			Throw new Exception(
				"$message at line $line_number in $filename" , 
				500
			); 
		}

		

	}
	
	// all non-catched exception are going thru this method
	public static function catchException(
		\Throwable $exception
	) {

		// if the router has an exception route
		if(Router::hasRoute('exception-handler')) {
			// store the exception so that the error controller 
			// can format it properly later on
			Store\Request::put('exception', $exception, true);
			// dispatch to the exception handler route
			Router::forward(Router::getRoute('exception-handler'));
			// prevent any further processing of code after the rendering of the exception
			Response::render();
		}
		// no exception handler is defined, we don't bother handling it
		else {

			// hard set the error code
			header(
				Request::server('SERVER_PROTOCOL') . 
				' 500 Internal Server Error', 
				true, 
				500
			);
			// hard set cache restriction
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			// throw exception normally
			Throw $exception; // doesn't that create a recursive loop?
		}
		
	}
	
	// this is able to catch catastrophic errors such as a parse error
	public static function catchCatastrophicError() :void {

		if(
			// if we encountered a PHP error
			$error = error_get_last() && 
			// and if we were not already handling the error/exception
			(Router::getCurrentRoute()->name ?? '') != 'exception-handler'
		){

			// redirect the exception handler
			Throw new \Exception(
				"{$error['message']} at line {$error['line']} in {$error['file']}", 
				500
			);
		}

	}

	public static function convertTraceToHtml(
		$exception
	) :string {

		$stack = '<div class="card-body">';

		$trace = $exception->getTrace();

		// use polyfony Elements instead
		foreach($trace as $index => $call) {
			// in case the stack element is not from an object
			$class 	= isset($call['class']) ? $call['class'] : '';
			$type 	= isset($call['type']) ? $call['type'] : '';
			// shorten the file path to lower cognitive load
			$file 	= !isset($call['file']) ?: str_replace('/var/www/', '', $call['file']); 
			// format a clean call
			$stack .= 
			"<span class=\"index-and-class\">{$index} {$class}<strong>{$type}{$call['function']}</strong></span> ";
			$stack .= 
			!isset($call['line']) ?: "<span class=\"badge bg-warning text-black\">@line {$call['line']}</span> in <span class=\"text-secondary\">{$file}</span><br />";
		}

		return "$stack</div>";

	}
	/*
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
		return $this->getMessage();
		
	}
*/
	public static function isAttentionRequiring($exception) :bool {
		return 
			$exception->getCode() < 401 || 
			$exception->getCode() > 404 || 
			Config::isDev();
	}

	public static function getIcon($exception) :string {

		// error is of type forbidden/bad request
		if(
			$exception->getCode() >= 400 && 
			$exception->getCode() <= 403
		) {
			$icon = 'fa fa-ban';
		}
		// error is of type internal error
		elseif(
			$exception->getCode() >= 500 && 
			$exception->getCode() <= 599
		) {
			$icon = 'fa fa-bug';
		}
		// error is not found
		elseif(
			$exception->getCode() == 404
		) {
			$icon = 'fa fa-exclamation-circle';
		}
		// any other type of errors
		else {
			$icon = 'fa fa-exclamation-triangle';
		}

		return $icon;

	}
	
}

?>
