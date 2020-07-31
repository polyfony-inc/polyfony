<?php
 
namespace Polyfony;

class Route {

	// url to match
	public $url				= '';
	// name of that route
	public $name			= null;
	// bundle destination
	public $bundle			= null;
	// controller destination
	public $controller		= null;
	// (optional) action destination
	public $action			= null;
	// (optional) url variable to trigger action
	public $trigger			= null;
	// (optional)method to match 
	public $method			= null;
	// (optional) redirection url
	public $redirectToUrl	= null;
	// (optional) redirection status code
	public $redirectStatus 	= null;

	// information deduced dynamically, for route matching
	public $staticSegment 					= '';
	public $indexedParameters				= [];
	public $indexedParametersConstraints 	= [];
	public $parametersConstraints 			= [];

	// construct the route given its name
	public function __construct(
		string $name = null
	) {
		$this->name = $name ?: uniqid('route-');
	}
	
	// set the url to match
	public function setUrl(
		string $url = null
	) :self {
		$this->url = $url;
		return $this;
	}

	public function setRedirect(
		string $destination_url, 
		int $redirect_status = 301
	) {
		$this->redirectToUrl 	= $destination_url;
		$this->redirectStatus 	= $redirect_status;
		return $this;
	}

	public function redirectIfItIsOne() :void {
		// if we are a redirection
		if($this->redirectToUrl) {
			Response::setStatus($this->redirectStatus);
			Response::setRedirect($this->redirectToUrl, 0);
			Response::setType('text');
			Response::render(); 
		}
	} 

	public function setMethod(
		string $method = null
	) :self {
		$this->method = in_array(
			strtolower($method), 
			Request::METHODS
		) ? strtolower($method) : null;
		return $this;
	}

	// set an associative array of contraints for the url parameters
	public function where(
		array $parameters_constraints
	) :self {
		$this->parametersConstraints = $parameters_constraints;
		// build constraincts to ease the job of the router later on
		$this->buildConstraints();
		// return the route
		return $this;
	}

	// shortcut for destination
	public function setDestination(
		string $merged_destination
	) :self {
		// explode the parameters
		list(
			$this->bundle, 
			$this->controller, 
			$this->action
		) = 
			explode(
				'/', 
				str_replace(
					'@', 
					'/',
					$merged_destination
				)
			);
		// if the action is an url parameter
		if(Route\Helper::isThisAParameterName($this->action)) {
			// we have an action triggered by an url parameter, remove special chars, and set the trigger
			$this->trigger = trim($this->action, '{}');
			// action is not known yet
			$this->action = null;
		}
		// build route segments tp ease the job of the router later on
		$this->buildSegments();
		// return the route
		return $this;
	}

	private function buildConstraints() :void {
		// flip the parameters array, to deduce each contraints index
		$reversedIndexedParameters = array_flip($this->indexedParameters);
		// for each of the constraints
		foreach(
			$this->parametersConstraints as 
			$parameter_name => $contraint_s
		) {
			// place it, in a array indexed by the parameter's position in theurl
			$this->indexedParametersConstraints[$reversedIndexedParameters[$parameter_name]] = $contraint_s;
		}
		// remove named constraints, they are not useful anymore
		$this->parametersConstraints = null;
	}

	private function buildSegments() :void {
		// if we have parameters in the url
		if(strstr($this->url, ':') !== false) {
			// explode all parameters from the route
			$list_of_parameters = explode(':', $this->url);
			// define the static segment for that route
			$this->staticSegment = $list_of_parameters[0];
			// remove the first parameter, as it's the base
			unset($list_of_parameters[0]);
			// for each of the route parameters
			foreach($list_of_parameters as $index => $parameter_name) {
				// push it to the list
				$this->indexedParameters[$index-1] = trim($parameter_name, '/');
				
			}
		}
		else {
			$this->staticSegment = $this->url;
		}
	}

	// FOR ROUTE MATCHING
	public function validatesTheseParameters(
		array $indexed_request_parameters
	) :bool {
		// for each of the parameters to validate
		foreach($indexed_request_parameters as $index => $value) {
			// if it fails to validate
			if(!$this->validateThisParameter($index, $value)) {
				return false;
			}
		}
		// if we got there, they all passed
		return true;
	}

	// FOR ROUTE MATCHING
	public function validateThisParameter(
		$parameter_index, 
		$parameter_value
	) :bool {
		// if a constraint exists for a parameter in that position
		if(isset($this->indexedParametersConstraints[$parameter_index])) {
			// for each of the constraints to check against
			foreach(
				$this->indexedParametersConstraints[$parameter_index] as 
				$constraint_type => $constraint_value
			) {
				// remove the constraint type value, if we are iterating over a value set without key
				$constraint_type = is_numeric($constraint_type) ? null : $constraint_type;
				// check for each constraint type
				if(
					$constraint_type == 'in_array' && 
					!in_array($parameter_value, $constraint_value)
				) {
					return false;
				}
				if(
					$constraint_value == 'is_numeric' && 
					!is_numeric($parameter_value)
				) {
					return false;
				}
				if(
					$constraint_type == '!in_array' && 
					in_array($parameter_value, $constraint_value)
				) {
					return false;
				}
				if(
					$constraint_value == '!is_numeric' && 
					is_numeric($parameter_value)
				) {
					return false;
				}
				if(
					$constraint_type == 'preg_match' && 
					!preg_match($constraint_value, $parameter_value)
				) {
					return false;
				}
				if(
					$constraint_type == '!preg_match' && 
					preg_match($constraint_value, $parameter_value)
				) {
					return false;
				}
			}
		}
		return true;

	}

	// FOR ROUTE MATCHING
	public function hasMethod(
		string $method
	) :bool {
		// if the method is undefined or it doesn't match the one defined
		return !$this->method || $this->method == $method;
	}

	// FOR ROUTE MATCHING
	public function hasStaticUrlSegment(
		string $url
	) :bool {
		// if the route is dynamic (has parameters), and starts with the base segment or if it matches strictly
		return 
			(
				$this->indexedParameters && 
				strpos($url, $this->staticSegment) === 0
			) || 
			$this->url == $url;
	}

	public function getDestination() :array {
		return [
			// destination script
			Request::server('DOCUMENT_ROOT') . 
			"/../Private/Bundles/{$this->bundle}/Controllers/{$this->controller}.php",
			// destination controller class
			'\\Controllers\\'.$this->controller,
			// destination controller method
			$this->action ? $this->action : 'index'
		];
	}

	public function deduceAction() :void {
		// if no action has been defined and a trigger has
		if(!$this->action && $this->trigger) {
			// we deduce the action from a request parameter
			$this->action = Request::get($this->trigger) ?: 'index';
		}
	}

	public function sendNamedParametersToRequest(
		$indexed_request_parameters
	) :void {
		// for each of the request parameters
		foreach(
			$indexed_request_parameters as 
			$index => $parameter_value
		) {
			// if the parameter exists in the route
			if(array_key_exists($index, $this->indexedParameters)) {
				// pass it to the request static class
				Request::setUrlParameter(
					$this->indexedParameters[$index], 
					$parameter_value
				);	
			}
		}
	}

	// assemble an url using provided url parameters (if any)
	public function getAssembledUrl(
		array $parameters = [], 
		bool $is_absolute = false, 
		bool $force_tls = false
	) :string {
		// declare a variable for the assembled url
		$url = $this->url;
		// for each of this url's possible parameters
		foreach(
			$this->indexedParameters as 
			$index => $parameter_name
		) {
			// if a replacement value has been provided
			$replacement = isset($parameters[$parameter_name]) ? 
				urlencode($parameters[$parameter_name]) . '/' : '';
			// replace with the value in the url, or remove the parameter placeholder
			$url = str_replace(
				[":{$parameter_name}/",'{'.$parameter_name.'}'] , 
				$replacement , 
				$url
			);
		}
		// return the assembled route with an absolute prefix if necessary
		return $is_absolute ? Route\Helper::prefixIt($url, $force_tls) : $url;

	}

}
