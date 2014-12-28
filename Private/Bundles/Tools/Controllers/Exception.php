<?php

use Polyfony as pf;
use Polyfony\Store as st;

// new example class to realize tests
class ExceptionController extends Polyfony\Controller {
	
	// this exception action is quite generic and can be kept for production
	public function exceptionAction() {
		
		// error occured while requesting something else than html
		if(!in_array(pf\Response::getType(),array('html','html-page'))) {
			// change the type
			pf\Response::setType('json');
			// set the stack a string
			pf\Response::setContent(pf\Store\Request::get('exception')->getTraceAsString());
			// render as is
			pf\Response::render();
		}
		// error occured normally
		else {
			// css neat style
			pf\Response::setAssets('css','//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
			// proper title
			pf\Response::setMetas(array(
				'title'=>st\Request::get('exception')->getMessage()
			));
			// grab some infos about the exception
			$this->Notice = new Polyfony\Notice\Danger(
				st\Request::get('exception')->getMessage(),
				'Error '.st\Request::get('exception')->getCode()
			);
			// add a bootstrap table view the view, with the full trace !
			$this->Exception = st\Request::get('exception');
			// pass to the exception view
			$this->view('Exception');
		}
			
	}
	

}

?>
