<?php


// new syntax for the login route (the route name is now optional)
Polyfony\Router::get('/login/','Demo/Demo@login');


// new syntax, with method restriction built in (GET), 
// we can map a post request on / to a totaly different bundle/controller@action
Polyfony\Router::get('/','Demo/Demo@welcome', 'index');


// new syntax
Polyfony\Router::map('/demo/:type/', 'Demo/Demo@{type}', 'demo')
	->where([
		'type'=>[
			'in_array'=>[
				'secure',
				'disconnect',
				'database',
				'locales',
				'request',
				'response',
				'exception',
				'json',
				'router',
				'vendorBootstrap',
				'vendorGoogle',
				''
			]
		]
	]);

?>
