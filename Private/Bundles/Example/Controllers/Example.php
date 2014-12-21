<?php

// new example class to realize tests
class ExampleController extends Polyfony\Controller {

	public function indexAction() {
	
		echo '<center><b style="font-size:120px;">pf2</b></center>';
		
	}
	
	public function testAction() {
		
		echo 'test';
			
	}
	
	public function dynamicAction() {
	
		echo 'Dynamic !';
		
	}
	
	public function errorAction() {
		
		//$exception = Polyfony\Store\Request::get('exception');
		
		// format the error
		echo '<h1>Internal server error</h1>';
		echo '<p>Hum…</p>';
		
	
			
	}

}

?>