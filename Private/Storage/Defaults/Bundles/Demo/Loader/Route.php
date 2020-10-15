<?php

namespace Polyfony;

// new syntax for the login route (the route name is now optional)
Router::get(
	'/login/',
	'Demo/Demo@login'
);


// new syntax, with method restriction built in (GET), 
// we can map a post request on / to a totaly different bundle/controller@action
Router::get(
	'/',
	'Demo/Demo@welcome', 
	'index'
);


// new syntax
Router::map(
	'/demo/:category/', 
	'Demo/Demo@{category}', 
	'demo'
)->where([
	'category'=>['in_array'=>[
		'',
		'secure',
		'locales',
		'emails',
		'database',
		'response',
		'request',
		'router',
		'logs',
		'exception',
		'json',
		'disconnect',
		'vendorBootstrap',
		'vendorGoogle'
	]]
]);

// use an existing favicon elsewhere
Router::redirect(
	'/favicon.ico',
	'/Assets/Img/Demo/favicon.png'
);

?>
