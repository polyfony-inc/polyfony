<?php

// optional shortcut to access the framework's classes
use Polyfony as pf;

// optional shortcut to access the store classes
use Polyfony\Store as Store;

// new example class to realize tests
class ExampleController extends Polyfony\Controller {

	public function indexAction() {
	
		echo 'Hello world';
		
		
	}
	
}

?>