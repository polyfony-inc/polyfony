<?php

// declare a new static route for the main index
Polyfony\Router::addRoute('index')
	->url('/')
	->destination('Demo','Demo','welcome');


// declare a new route with a name « demo » for all demos
Polyfony\Router::addRoute('demo')
	// define the url structure (with named parameters
	->url('/demo/:type/:something/')
	// restrict some parameters values
	->restrict(array(
		// the type, if set, can only be on of those or the roule will not match
		'type'=>array('login','secure','database','locales','request','response','exception','json','xml')
	))
	// set the destination for that route, Bundle, Controller, (optional) Action
	->destination('Demo','Demo')
	// set an optional parameter that will trigger an action
	->trigger('type');

// a shortcut for the login route
Polyfony\Router::addRoute('login')
	->url('/login/')
	->destination('Demo','Demo','login');


?>
