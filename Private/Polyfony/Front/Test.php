<?php

namespace Polyfony\Front;

 // this replaces the usual Front class, for running tests in CLI with PHPUnit
class Test {
	
	// upon construction
	public function __construct() {
		
		// if we are not already in the pulic directory
		if(basename(getcwd()) != 'Public') {
			// change the working directory to emulate a Public request
			chdir('Public');
		}
		// detect context CLI/WEB
		\Polyfony\Request::init();	
		
		// detect env and load .ini files accordingly
		\Polyfony\Config::init();

	}

}

?>
