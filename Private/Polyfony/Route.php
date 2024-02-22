<?php
 
namespace Polyfony;

class Route {

	// url to match
	public ?string $url						= null;
	// name of that route
	public ?string $name					= null;
	// bundle destination
	public ?string $bundle					= null;
	// controller destination
	public ?string $controller				= null;
	// (optional) action destination
	public ?string $action					= null;
	// (optional) url variable to trigger action
	public ?string $trigger					= null;
	// (optional)method to match 
	public ?string $method					= null;
	// (optional) redirection url
	public ?string $redirectToUrl			= null;
	// (optional) redirection status code
	public ?int $redirectStatus 			= null;
	// (optional) if we want to sign the url
	public bool $signing 					= false;
	// (optional) if we want url to expire
	public bool $expiring					= false;
	// (optional) how long these URLs are valid
	public ?int $expiringTtl				= null;
	// (optional) how many hits allowed
	public ?int $throttleLimitTo 			= null;
	// (optional) in what timeframe are they allowed
	public ?int $throttleTimeframe 			= null;

	// information deduced dynamically, for route matching
	public ?string $staticSegment 				= '';
	public array $indexedParameters				= [];
	public array $indexedParametersConstraints 	= [];
	public array $parametersConstraints 		= [];

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

	public function throttle(
		int $limit_to, 
		int $timeframe
	) :self {
		// check that the route is not auto-named
		if(stripos($this->name, 'route-') === 0) {
			Throw new Exception(
				'You must explicitely name the route to use throttling', 
				500
			);
		}
		// set the throttling parameters
		$this->throttleLimitTo = $limit_to;
		$this->throttleTimeframe = $timeframe;
		// chain
		return $this;
	}

	public function sign() :self {
		// save the fact that we want to sign
		$this->signing = true;
		// add a "signature" parameter to the existing url
		$this->setUrl(
			'/'. trim($this->url,'/') . 
			'/:'.Config::get('router','signature_parameter_name').'/'
		);
		// re-build segments (not ideal for performances... but generated routes get cached anyways)
		$this->buildSegments();
		// add a length constraint on the signature parameter
		$this->where([
			Config::get('router','signature_parameter_name')=>[
				'strlen'=>Config::get('hashs','length')
			]
		]);
		// chain
		return $this;
	}

	public function expires(int $ttl_in_seconds) :self {
	
		// if signature has already been declared
		if($this->signing) {
			// this cannot be allowed
			trigger_error('->sign() must be invoked after ->expires()');
		}
		// save the fact that we want these urls to expire
		$this->expiring = true;
		// save the ttl
		$this->expiringTtl = $ttl_in_seconds;
		// add an expiration timestamp parameter to the existing url
		$this->setUrl(
			'/'. trim($this->url,'/') . 
			'/:'.Config::get('router','url_parameters_expiration').'/'
		);
		// re-build segments (not ideal for performances... but generated routes get cached anyways)
		$this->buildSegments();
		// add a typing constraint on the expiration parameter
		$this->where([
			Config::get('router','url_parameters_expiration')=>[
				'is_numeric'
			]
		]);
		// chain
		return $this;

	}

	public function throttleIfAny() :void {
		// if we have throttling restrictions
		if(
			$this->throttleLimitTo && 
			$this->throttleTimeframe && 
			$this->name
		) {
			Throttle::enforce(
				$this->throttleLimitTo, 
				$this->throttleTimeframe,
				Hashs::get([
					$this->name,
					Request::server('REMOTE_ADDR')
				])
			);
		}
	}

	public function redirectIfAny() :void {
		// if we are a redirection
		if($this->redirectToUrl) {
			Response::setStatus($this->redirectStatus);
			Response::setRedirect($this->redirectToUrl, 0);
			Response::setType('text');
			Response::render(); 
		}
	} 

	public function setMethod(
		?string $method = null
	) :self {
		$this->method = in_array(
			strtolower($method ?? ''), 
			Request::METHODS
		) ? strtolower($method) : null;
		return $this;
	}

	// set an associative array of contraints for the url parameters
	public function where(
		array $parameters_constraints
	) :self {
		$this->parametersConstraints = array_replace(
			$this->parametersConstraints, 
			$parameters_constraints
		);
		// build constraints that will be cached, to ease the job of the router later on
		$this->buildConstraints();
		// return the route
		return $this;
	}

	// shortcut for destination
	public function setDestination(
		string $bundle_controller_action
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
					$bundle_controller_action
				)
			);
		// if the action is an url parameter
		if(Route\Helper::isThisAParameterName($this->action)) {
			// we have an action triggered by an url parameter, remove special chars, and set the trigger
			$this->trigger = trim($this->action, '{}');
			// action is not known yet
			$this->action = null;
		}
		// build route segments to ease the job of the router later on
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
			// place it, in a array indexed by the parameter's position in the url
			$this->indexedParametersConstraints[
				$reversedIndexedParameters[$parameter_name]
			] = $contraint_s;
		}
	}

	private function buildSegments() :void {
		// if we have parameters in the url
		if(strstr($this->url ?? '', ':') !== false) {
			// explode all parameters from the route
			$list_of_parameters = explode(':', $this->url);
			// define the static segment for that route
			$this->staticSegment = $list_of_parameters[0];
			// remove the first parameter, as it's the base
			unset($list_of_parameters[0]);
			// for each of the route parameters
			foreach(
				$list_of_parameters as 
				$index => $parameter_name
			) {
				// push it to the list
				$this->indexedParameters[$index -1] = trim($parameter_name, '/');
			}
		}
		else {
			$this->staticSegment = $this->url;
		}
	}

	// FOR ROUTE MATCHING
	public function hasValidParameters(
		array $request_parameters_indexed_by_position
	) :bool {
		// for each of the parameters to validate
		foreach(
			$request_parameters_indexed_by_position as 
			$index => $value
		) {
			// if it fails to validate
			if(!$this->validateThisParameter($index, $value)) {
				return false;
			}
		}
		// if we got there, they all passed
		return true;
	}

	// FOR ROUTE MATCHING
	public function hasValidSignature(
		array $request_parameters_indexed_by_position
	) :bool {
		
		// if the route needs a signature to be a match
		if($this->signing) {

			// if we don't even have the same number of provided vs. expected parameters
			if(
				count($this->indexedParameters) != 
				count(array_filter($request_parameters_indexed_by_position))
			) {
				// don't bother going futher
				return false; 
			}

			// combine URL parameters names (keys) with their values
			$associative_parameters = array_combine(
				$this->indexedParameters, 
				array_filter($request_parameters_indexed_by_position)
			);

			// get the current signature
			$url_signature = $associative_parameters[Config::get('router','signature_parameter_name')];

			// remove the signature from the parameters
			unset($associative_parameters[Config::get('router','signature_parameter_name')]);

			// compare the provided signature with a newly generated one
			$is_signature_valid = Hashs::compare(
				$url_signature,
				[
					$this->name,
					$associative_parameters
				]
			);

			// if the signing signature is valid
			if($is_signature_valid) {
			
				// if the route is expiring
				if($this->expiring) {
				
					// if the expiration is in the future
					if($associative_parameters[Config::get('router','url_parameters_expiration')] > time()) {
						// route is valid
						return true;
					}
					// route has expired
					else {
						// throw a 410 Gone.
						Throw new Exception('This link has expired', 410);
					}

				}
				// the route does not expire
				else {
					// and signature is valid
					return true;
				}
			
			}
			// invalid signature
			else {
				return false;
			}

		}
		// the route doesn't need a signature, we consider it's always OK
		else {
			return true;
		}
		
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
					$constraint_type == 'strlen' && 
					mb_strlen($parameter_value) != $constraint_value
				) {
					return false;
				}
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
	public function hasStaticSegment(
		string $url
	) :bool {
		// if the route is dynamic (has parameters), and starts with the base segment or if it matches strictly
		return 
			// PHP8 str_starts_with()
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
			$this->controller . 'Controller',
			// destination controller method
			$this->action ? $this->action : 'index'
		];
	}

	private function deduceAction() :void {
		// if no action has been defined and a trigger has
		if(!$this->action && $this->trigger) {
			// we deduce the action from a request parameter (with removal of special chars to prevent abuse)
			$this->action = Format::fsSafe(Request::get($this->trigger, 'index') ?: 'index');
		}
	}

	private function forwardUrlParametersToRequest(
		$request_parameters_indexed_by_position
	) :void {
		// for each of the request parameters
		foreach(
			$request_parameters_indexed_by_position as 
			$index => $parameter_value
		) {
			// if the parameter exists in the route
			if(array_key_exists($index, $this->indexedParameters)) {
				// forward it to the request static class
				Request::setUrlParameter(
					$this->indexedParameters[$index], 
					$parameter_value
				);	
			}
		}
	}

	public function setAsMatching(
		array $request_parameters_indexed_by_position = []
	) :void {
		// send the named parameters to the request class
		$this->forwardUrlParametersToRequest($request_parameters_indexed_by_position);
		// deduce the dynamic action from the url parameters if necessary
		$this->deduceAction();
	}

	// assemble an url using provided url parameters (if any)
	public function getAssembledSegments(
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
		return $is_absolute ? 
			Route\Helper::prefixIt($url, $force_tls) : 
			$url;

	}

}
