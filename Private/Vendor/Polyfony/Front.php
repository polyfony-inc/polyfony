<?php
/**
 * PHP Version 5
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;
 
class Front {
	
	// upon construction
	public function __construct() {
		
		// start the profiler
		Profiler::init();
		
		// detect context CLI/WEB and set the proper url
		Request::init();
		
		// marker
		Profiler::setMarker('init_request');
		
		// detect env and load .ini files accordingly
		Config::init();
		
		// start the exeption catcher (quite late, but we gotta know if config has use_strict)
		Exception::init();
		
		// marker
		Profiler::setMarker('init_config');

		// marker
		Profiler::setMarker('init_cache');

		// prepare a response with defaults parameters
		Response::init();
		
		// marker
		Profiler::setMarker('init_response');
		
		// look for runtime and routes in the bundles
		Bundles::init();
		
		// marker
		Profiler::setMarker('init_bundles');
		
		// route the request to a matching bundle/controller/action if any
		Router::init();
		
		// render the response if it has not already been done in a controller
		Response::render();
		
	}
		
}


?>
