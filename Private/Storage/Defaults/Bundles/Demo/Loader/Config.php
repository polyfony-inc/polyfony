<?php

use Polyfony as pf;

// set some demo data
pf\Config::set('demo', 'some_key', 'some_value');



Polyfony\Events::register(
	'onTerminate', 
	function(){ 
		Models\Emails::sendAllPending();
	}
);

?>
