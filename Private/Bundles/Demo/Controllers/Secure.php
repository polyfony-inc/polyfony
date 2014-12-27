<?php

use Polyfony as pf;
use Polyfony\Store as st;

// new example class to realize tests
class SecureController extends pf\Controller {

	
	public function indexAction() {

		pf\Security::enforce();
		echo 'Secure';
		
	}
		

}

?>