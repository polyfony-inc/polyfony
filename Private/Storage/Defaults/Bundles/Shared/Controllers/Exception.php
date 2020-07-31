<?php

// all controllers are now isolated in the Controllers namespace
namespace Controllers;

use \Polyfony\{ 
	Security, Response, Request, Element, Store, 
	Router, Form, Config, Logger, Controller, Locales
};

// new example class to realize tests
class Exception extends Controller {
	
	// this exception action is quite generic and can be kept for production
	public function exception() {

		// get the exception
		$exception = Store\Request::get('exception');
		

		// change the status code of the response
		Response::setStatus(
			$exception->getCode() ? 
				$exception->getCode() : 
				500
		);

		// if the exception is not a 404 or a 403 (the most common and non important)
		if(\Polyfony\Exception::isAttentionRequiring($exception)) {
			// log the exception
			Logger::critical(
				$exception->getCode() . ' '.
				$exception->getMessage(), 
				$exception->getTrace() // !!!!!! THIS MAY NEED TO BE CHANGED !!!!!
			);
		}
		

		// error occured while requesting something else than html
		if(!in_array(
			Response::getType(), 
			['html','html-page']
		)) {
			// change the type to plaintext
			Response::setType('text');
			// set the stack a string
			Response::setContent(
				$exception->getCode() . "\n".
				$exception->getMessage() . "\n".
				$exception
			);
			// render as is
			Response::render();
		}
		// error occured normally
		else {
			// disable space removal for the trace stack
			Config::set('response','minify', 0);
			// reset already output data (very important, because of content-length)
			Response::setType('html-page');
			// proper title and assets
			Response\HTML::set([
				'links'		=>Config::get('response', 'links'),
				'scripts'	=>Config::get('response', 'scripts'),
				'metas'		=>[
					'title'=> 
						$exception->getCode() . ', ' . 
						Locales::get($exception->getMessage())
				],
			]);
			
			// pass to the exception view
			$this->view('Exception', [
				'requires_attention'	=>\Polyfony\Exception::isAttentionRequiring($exception),
				'message'				=>$exception->getMessage(),
				'file'					=>$exception->getFile(),
				'line'					=>$exception->getLine(),
				'code'					=>$exception->getCode(),
				'icon'					=>\Polyfony\Exception::getIcon($exception),
				'html_trace'			=>\Polyfony\Exception::convertTraceToHtml($exception),
				'string_trace'			=>$exception->getTraceAsString()
			]);

		}
			
	}
	

}

?>
