<?php

// this will match strictly /demo/
Polyfony\Router::addRoute('main-index')
	->url('/')
	->destination('Demo','Demo','index');


// this will match strictly /demo/
Polyfony\Router::addRoute('demo-index')
	->url('/demo/')
	->destination('Demo','Demo','demo');


// this will match strictly /demo/
Polyfony\Router::addRoute('demo-login')
	->url('/login/')
	->destination('Demo','Demo','login');

// this will match strictly /demo/
Polyfony\Router::addRoute('demo-secure')
	->url('/demo/secure/:option/')
	->destination('Demo','Secure')
	->trigger('option');

/*
// this route will match only if option is (create or update or delte)
Polyfony\Router::addRoute('demo-restricted')
	->url('/demo/restricted/:option/')
	->restrict(array(
		'option'=>array('create','update','delete')
	))
	->destination('Demo','Demo','restricted');

// this route will match only if option is set in the url
Polyfony\Router::addRoute('demo-required')
	->url('/demo/required/:option/')
	->restrict(array(
		'option'=>true
	))
	->destination('Demo','Demo','required');

// this route will match anything starting with /demo/dynamic/
// option will trigger a method in the class Main
Polyfony\Router::addRoute('demo-dynamic')
	->url('/demo/dynamic/:option/')
	->destination('Demo','Main')
	->trigger('option');
*/
?>