<?php

namespace Polyfony;
 
class Front {
	
	// upon construction
	public function __construct() {
		
		// start the profiler
		Profiler::init();

		// detect context CLI/WEB and set the proper url
		Request::init();	
		
		// detect env and load .ini files accordingly
		Config::init();

		// init the response so that is can render a cached one
		Response::init();

		// start the exeption catcher (quite late, but we gotta know if config has use_strict)
		Exception::init();
		
		// look for runtime and routes in the bundles
		Bundles::init();
		
		// route the request to a matching bundle/controller/action if any
		Router::init();
		
		// render the response if it has not already been done in a controller
		Response::render();
		
	}
		
}


?>
