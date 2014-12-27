<?php

use Polyfony as pf;

// new example class to realize tests
class SecureController extends pf\Controller {

	
	public function indexAction() {

		pf\Security::enforce();
		echo 'Secure';
		
	}
		

}

?>
