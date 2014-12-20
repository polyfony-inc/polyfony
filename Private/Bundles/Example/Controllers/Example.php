<?php

class ExampleController extends Polyfony\Controller {

	public function indexAction() {
	
		echo 'Hello world';
		
		Throw new Polyfony\Exception('Fuck');
		
		
	}
	
}

?>