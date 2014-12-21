<?php


Polyfony\Router::addRoute('test')
	->url('/test/:foo/:bar/')
	->restrict(array(
		'bar'=>'@numeric'
	))
	->destination('Example','Example','index')
	->trigger('foo');



Polyfony\Router::addRoute('somestaticroute')
	->url('/a-propos/')
	->destination('Demo','Demo','index');

?>