<?php

// declare the main exceptions route (it doesn't have to have an url)
Polyfony\Router::map(null, 'Tools/Exception@exception', 'exception');

// declare tools bundle URL
Polyfony\Router::get('/tools/:section/:option/', 'Tools/Main@{section}');

?>
