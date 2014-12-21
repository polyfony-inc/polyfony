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
		

		// format the error
		echo "<h1>Exception occured</h1>";
		echo Polyfony\Store\Request::get('exception')->getCode();
		echo Polyfony\Store\Request::get('exception')->getMessage();
	
			
	}

}

?>