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
		echo pf\Format::size(memory_get_usage());
			
	}
	
	public function dynamicAction() {
	
		echo 'Dynamic !';
		
	}
	
	public function errorAction() {
		
	//	$exception = Polyfony\Store\Request::has('exception') ? Polyfony\Store\Request::has('exception') : array('message'=>'Internal serveur error','code'=>'500');
		
		// format the error
		echo "<h1>Internal server error</h1>";
	
			
	}

}

?>