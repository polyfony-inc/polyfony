<?php

use Polyfony as pf;
use Polyfony\Store as st;

// new example class to realize tests
class ExampleController extends Polyfony\Controller {

	public function preAction() {
		
		pf\Response::setMetas(array('title'=>'Polyfony 2'));
		pf\Response::setAssets('css','//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
		
	}

	public function indexAction() {

		// view the main index/welcome page
		$this->view('Polyfony');
		
	}
	
	public function testAction() {

		Throw new pf\Exception('You are not allowed here',403);
		
	}
	
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