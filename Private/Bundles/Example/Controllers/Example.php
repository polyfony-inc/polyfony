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
			// format the error
			echo "<h1>Exception occured</h1>";
			echo pf\Store\Request::get('exception')->getCode();
			echo pf\Store\Request::get('exception')->getMessage();
			echo '<br />';
			echo pf\Store\Request::get('exception')->getTraceAsString();
		}
			
	}

}

?>