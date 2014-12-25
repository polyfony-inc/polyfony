<?php


// declare the main exceptions route (it doesn't have to have an url)
Polyfony\Router::addRoute('exception')
	->destination('Example','Example','exception');

// the / route to display a welcome message
Polyfony\Router::addRoute('polyfony-index')
	->url('/')
	->destination('Example','Example');

// a test url
Polyfony\Router::addRoute('test')
	->url('/test/')
	->destination('Example','Example','test');


?>