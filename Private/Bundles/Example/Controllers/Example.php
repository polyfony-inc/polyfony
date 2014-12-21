<?php

use Polyfony as pf;
use Polyfony\Store as st;

// new example class to realize tests
class ExampleController extends Polyfony\Controller {

	public function preAction() {
		
		pf\Response::setMetas(array('title','Polyfony2'));
		pf\Response::setAssets('css','/Assets/bootstrap.css');
		
	}

	public function indexAction() {
	
		$this->view('Polyfony');
		
	}
	
	public function helloAction() {
		
		$this->view('HelloWorld');
			
	}
	
	public function testAction() {
		
		echo 'test<br />';
			
	}
	
	public function dynamicAction() {
	
		echo 'Dynamic !';
		
	}
	
	public function exceptionAction() {
		
		// error occured in ajax
		if(Polyfony\Request::isAjax()) {
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
				'title'=>'Exception occured'
			));
			// grab some infos about the exception
			$this->Code = st\Request::get('exception')->getCode();
			$this->Message = st\Request::get('exception')->getMessage();
			$this->Trace = st\Request::get('exception')->getTraceAsString();
			// pass to the exception view
			$this->view('Exception');
		}
			
	}
	
	public function postAction() {
		
		//echo 'EOF';	
		
	}

}

?>