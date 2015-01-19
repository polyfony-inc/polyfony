<?php

// declare the main exceptions route (it doesn't have to have an url)
Polyfony\Router::addRoute('exception')
	->destination('Tools', 'Exception', 'exception');

// declare tools bundle URL
Polyfony\Router::addRoute('tools')
	->url('/tools/:section/:option/')
	->destination('Tools', 'Main')
	->trigger('section');

?>
