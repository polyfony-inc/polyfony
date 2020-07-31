<?php

// declare the main exceptions route 
// (it doesn't have to have an url)
Polyfony\Router::map(
	null, 
	'Shared/Exception@exception', 
	'exception-handler'
);

?>
