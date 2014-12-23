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
	
	public function noticeAction() {
	
		// this works !
		var_dump(
			Bundles\Example\Model\Users::all(),
			Bundles\Example\Model\Users::withLevel(1)
		);
	
		/*echo new Polyfony\Notice('test');*/
		
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
			$this->Exception = st\Request::get('exception');
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