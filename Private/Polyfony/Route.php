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
	// (optional) parameter restriction
	public $restrictions	= [];
	// (optional) redirection url
	public $redirect		= null;
	// (optional) redirection status code
	public $redirectStatus 	= null;

	// construct the route given its name
	public function __construct(string $name = null) {
		$this->name				= $name ?: uniqid('route-');
	}
	
	// set the url to match
	public function url(string $url = null) :self {
		$this->url = $url;
		return $this;
	}

	public function redirectTo(string $destination_url) {
		$this->redirect = $destination_url;
		return $this;
	}

	public function redirectStatus(int $status) :self {
		$this->redirectStatus = $status;
		return $this;
	}

	public function redirectIfItIsOne() :void {
		// if we are a redirection
		if($this->redirect) {
			Response::setStatus($this->redirectStatus);
			Response::setRedirect($this->redirect, 0);
			Response::setType('text');
			Response::render(); 
		}
	} 

	public function method(string $method = null) :self {
		$this->method = in_array(strtolower($method), Request::METHODS) ? strtolower($method) : null;
		return $this;
	}

	// shortcut for method
	public function get() :self {
		return $this->method('get');
	}

	// shortcut for method
	public function post() :self {
		return $this->method('post');
	}

	// shortcut for method
	public function delete() :self {
		return $this->method('delete');
	}

	// shortcut for method
	public function put() :self {
		return $this->method('put');
	}

	// set an associative array of contraints for the url parameters
	public function where(array $restrictions) :self {
		$this->restrictions = $restrictions;
		return $this;
	}

	// shortcut for destination
	public function to($merged_destination) :self {
		// explode the parameters
		list($this->bundle, $controller_with_action) = explode('/', $merged_destination);
		// if the parameters are incomplete
		if(!$controller_with_action) {
			// we don't allow to proceed
			Throw new Exception('Route->to() should look like ("Bundle/Controller@method"');
		}
		// explode parameters further
		list($this->controller, $action) = explode('@', $controller_with_action);
		// if the action exists and contain a semicolon or is surounded by braquets
		if($action && (substr($action, 0, 1) == ':' || (substr($action, 0, 1) == '{') && substr($action, -1, 1) == '}')) {
			// we have an action triggered by an url parameter, remove special chars, and set the trigger
			$this->trigger = str_replace([':','{','}'],'', $action);
			// action is not known yet
			$this->action = null;
		}
		// the action exist, and it is a regual one
		elseif($action) {
			// define the action
			$this->action = $action;
		}
		// the action is totaly unspecified
		else {
			// so we use the index
			$this->action = 'index';
		}
		// return the route
		return $this;
	}

	public function hasMethodAndItIsNot(string $method) :bool {
		// if the method is defined, and it doesn't match
		return $this->method && $this->method != $method;
	}

	public function getUrlPortions() {
		return strstr($this->url,':') ? explode(':',$this->url) : $this->url;
	}

	public function getDestinationScript() :string {
		return "../Private/Bundles/{$this->bundle}/Controllers/{$this->controller}.php";
	}

	public function getDestinationControllerClass() :string {
		return "{$this->controller}Controller";
	}

	public function getDestinationControllerMethod() :string {
		return $this->action ? "{$this->action}Action" : 'indexAction';
	}

	public static function isThisAParameterName($unknown_string) :bool {
		return 
			// if we've got an old school parameter
			substr($unknown_string, 0, 1) == ':' || 
			(
				// or if we've got a new syntax parameter
				substr($unknown_string, 0, 1) == '{' && 
				substr($unknown_string, -1, 1) == '}'
			);
	}

	// assembles an url using provided url parameters
	public function getAssembledUrl(
		array $parameters=[], bool $absolute = false, bool $force_tls = false
	) :string {

		// declare a variable for the assembled url
		$url = $this->url;
		// for each provided parameter
		foreach($parameters as $variable => $value) {
			// replace it in the url in both possible forms
			$url = str_replace([":{$variable}/",'{'.$variable.'}'] , urlencode($value) . '/', $url);
		}
		// the list of all parameters present in the url
		$all_parameters = (array) explode('/', $url);
		// for each parameter
		foreach($all_parameters as $index => $a_parameter) {
			// if it starts with a semicolon or is enclosed by brackets
			if(self::isThisAParameterName($a_parameter)) {
				// if it has not been replaced by a value, we remove it
				$url = str_replace("{$a_parameter}/", '', $url);
			}
		}
		// define the protocol to use (use the current one, or https if it is forced)
		$protocol = 
			// if we are in prod and tls is to be enforced
			((Config::isProd() && $force_tls) || 
			// or if we already are rolling https
			Request::getProtocol() == 'https') ? 
			'https' : 'http';

		// if we want an absolute url, prefix with the domain and with the port if the protocol is not https
		!$absolute ?: $url = $protocol . '://' . Config::get('router', 'domain') . 
		(Request::getPort() != 80 && $protocol == 'http' ? ':'.Request::getPort() : '') . $url;

		return $url;

	}

}
