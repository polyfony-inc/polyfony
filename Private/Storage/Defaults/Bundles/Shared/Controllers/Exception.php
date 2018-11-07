<?php

use Polyfony as pf;
use Polyfony\Config as Config;
Use Polyfony\Response as Response;
use Polyfony\Locales as Locales;
use Polyfony\Store as Store;
use Polyfony\Logger as Logger;
// new example class to realize tests
class ExceptionController extends Polyfony\Controller {
	
	// this exception action is quite generic and can be kept for production
	public function exceptionAction() {
		
		// get the exception
		$this->Exception = Store\Request::get('exception');
		// if the exception is not a 404 or a 403 (the most common and non important)
		if(
			$this->Exception->getCode() < 401 || 
			$this->Exception->getCode() > 404
		) {
			// log the exception
			Logger::critical(
				$this->Exception->getCode() . ' '.
				$this->Exception->getMessage(), 
				$this->Exception->getTrace()
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
				$this->Exception->getCode() . "\n".
				$this->Exception->getMessage() . "\n".
				$this->Exception
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
				'metas'		=>[
					'title'=> $this->Exception->getCode() . ', ' . 
						Locales::get($this->Exception->getMessage())
				],
				'scripts'	=>[
					'//code.jquery.com/jquery-3.3.1.slim.min.js',
					'//stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js'
				],
				'links'		=>[
					'//stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css',
					'//use.fontawesome.com/releases/v5.3.1/css/all.css'
				]
			]);
			
			// error is of type forbidden/bad request
			if(
				$this->Exception->getCode() >= 400 && 
				$this->Exception->getCode() <= 403
			) {
				$this->icon = 'fa fa-ban';
			}
			// error is of type internal error
			elseif(
				$this->Exception->getCode() >= 500 && 
				$this->Exception->getCode() <= 599
			) {
				$this->icon = 'fa fa-bug';
			}
			// error is not found
			elseif(
				$this->Exception->getCode() == 404
			) {
				$this->icon = 'fa fa-exclamation-circle';
			}
			// any other type of errors
			else {
				$this->icon = 'fa fa-exclamation-triangle';
			}

			// pass to the exception view
			$this->view('Exception');
		}
			
	}
	

}

?>
