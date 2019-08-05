<?php

namespace Polyfony;

class Controller {


	// method to override
	public function preAction() {
		
	}
	
	// method to override
	public function postAction() {
		
	}

	// method to override
	public function indexAction() {
		
	}

	// method to override
	public function defaultAction() {
		// default will throw an exception
		throw new Exception('This action does not exist', 500);
	}
	
	// include a view
	final public function view(
		string $view_name, 
		$bundle_or_variables = null, 
		?array $variables = []
	) :void {
		
		// set bundle in which the view is
		$view_bundle = 
			$bundle_or_variables && is_string($bundle_or_variables) ? 
			$bundle : Router::getCurrentRoute()->bundle;

		// build the path for that view
		$view_path = "../Private/Bundles/{$view_bundle}/Views/{$view_name}.php";

		// import variables for the view
		extract(
			$bundle_or_variables && is_array($bundle_or_variables) ? 
			$bundle_or_variables : $variables
		);

		// if the file does not exist
		if(!file_exists($view_path)) {
			// throw an exception
			Throw new Exception(
				"Controller->view() View file does not exist [{$view_path}]", 
				500
			);
		}
		// the file exists
		else {
			// marker (note that the microtime is very far from being an acceptable solution)
			$id_marker = Profiler::setMarker(
				"{$view_bundle}/{$view_name}-".
				substr(microtime(true),-4,6), 'view'
			);
			// simply include it
			require($view_path);
			// marker
			Profiler::releaseMarker($id_marker);
		}
		
	}
	
	// forward to another controller in the same bundle
	final public function forward(string $controller, $action = null) :void {

		// get the current route as a base
		$route = Router::getCurrentRoute();
		
		// and alter it
		$route->controller	= $controller;
		$route->action		= $action;
		
		// forward to the new route
		Router::forward($route);
		
	}
	
}	

?>
