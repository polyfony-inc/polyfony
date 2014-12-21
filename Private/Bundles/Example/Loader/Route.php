<?php


Polyfony\Router::addRoute('test')
	->url('/test/:foo/:bar/:foobar/')
	->restrict(array(
		'foo'=>array('a','b'),
		'foobar'=>array('on','off')
	))
	->destination('Example','Example','index')
	->trigger('bar');



Polyfony\Router::addRoute('somestaticroute')
	->url('/a-propos/')
	->destination('Demo','Demo','index');

?>