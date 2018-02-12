<?php

use Polyfony as pf;
use Polyfony\Store as st;

// new example class to realize tests
class ExceptionController extends Polyfony\Controller {
	
	// this exception action is quite generic and can be kept for production
	public function exceptionAction() {
		
		// get the exception
		$this->Exception = pf\Store\Request::get('exception');

		// error occured while requesting something else than html
		if(!in_array(pf\Response::getType(), array('html','html-page'))) {
			// change the type to plaintext
			pf\Response::setType('text');
			// set the stack a string
			pf\Response::setContent(
				$this->Exception->getCode() . "\n".
				$this->Exception->getMessage() . "\n".
				$this->Exception
			);
			// render as is
			pf\Response::render();
		}
		// error occured normally
		else {
			// proper title
			pf\Response::setMetas(array(
				'title'=> $this->Exception->getMessage()
			));
			
			// css neat style
			pf\Response::setAssets('css','//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
			pf\Response::setAssets('css', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
			
			// js neat style
			pf\Response::setAssets('js','//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');
			
			// disable space removal for the trace stack
			pf\Config::set('response','minify', 0);
			
			// set the title or the error
			$this->Title = $this->Exception->getCode() . ', ' . pf\Locales::get($this->Exception->getMessage());
			
			// add a bootstrap table view the view, with the full trace !
			$this->Trace = $this->Exception;
			
			// error is of type forbidden/bad request
			if($this->Exception->getCode() >= 400 && $this->Exception->getCode() <= 403) {
				$this->icon = 'fa fa-ban';
			}
			// error is of type internal error
			elseif($this->Exception->getCode() >= 500 && $this->Exception->getCode() <= 599) {
				$this->icon = 'fa fa-bug';
			}
			// any other type of errors
			else {
				$this->icon = 'fa fa-warning';
			}

			// pass to the exception view
			$this->view('Exception');
		}
			
	}
	

}

?>
