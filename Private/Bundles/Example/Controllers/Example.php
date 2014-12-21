<?php

use Polyfony as pf;

// new example class to realize tests
class ExampleController extends Polyfony\Controller {

	public function preAction() {
		
		pf\Response::setMetas('title','Polyfony2');
		pf\Response::setAssets('css','/bootstrap.css');
		
	}

	public function indexAction() {
	
		echo '<center><b style="font-size:120px;">pf2</b></center>';
		
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
			$this->Code = pf\Store\Request::get('exception')->getCode();
			$this->Message = pf\Store\Request::get('exception')->getMessage();
			$this->Trace = pf\Store\Request::get('exception')->getTraceAsString();
			// pass to the exception view
			$this->view('Exception');
		}
			
	}
	
	public function postAction() {
		
		//echo 'EOF';	
		
	}

}

?>