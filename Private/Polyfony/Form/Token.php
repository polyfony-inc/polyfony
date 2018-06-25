<?php

namespace Polyfony\Form;
use \Polyfony\Config as Config;
use \Polyfony\Request as Request;
use \Polyfony\Store\Session as Session;
use \Polyfony\Keys as Keys;
use \Polyfony\Element as Element;
use \Polyfony\Response as Response;

class Token {

	// the actual value of the token
	private $value 	= null;

	// this will instanciate a token
	public function __construct() {

		// instancing a token will disable browser and framework caching
		// as to prevent mismatching tokens
		Response::disableBrowserCache();
		Response::disableOutputCache();

		// a unique token will be generated, and will live in the PHP session
		$this->value = Keys::generate(
			Config::get('form','token_name') . uniqid(false,true)
		);

		// store it in the current session
		Session::put(Config::get('form','token_name'), $this->value, true);

	}

	// get the value of the token, for manual use
	public function getValue() :string {

		// return as is
		return $this->value;

	}

	public function __toString() {

		// build a hidden input html element
		return (string) new Element('input', [
			'type'	=>'hidden',
			'name'	=>Config::get('form','token_name'),
			'value'	=>$this->value
		]); 

	}

	// this will check, upon posting the form, that it is legitimate
	public static function enforce() :void {

		// if the request is of type post
		if(Request::isPost()) {
			// is a token is present
			if(Request::post(Config::get('form','token_name'))) {
				// check the provided token against our legitimate tokens
				if(!self::isLegitimate(Request::post(
					Config::get('form','token_name')
				))) {
					// soft redirect to the previous page after a few seconds
					Response::setRedirect(Request::server('HTTP_REFERER'), 3);
					// throw an exception to prevent this action from succeeding
					Throw new \Polyfony\Exception('Polyfony/Form/CSRF::enforce() invalid CSRF Token');
				}
			}
			// missing token
			else {
				// soft redirect to the previous page after a few seconds
				Response::setRedirect(Request::server('HTTP_REFERER'), 3);
				// throw an exception to prevent this action from succeeding
				Throw new \Polyfony\Exception('Polyfony/Form/CSRF::enforce() missing CSRF Token');
			}
		}

	}

	private static function isLegitimate($token) :bool {

		// look for that token in the current session
		if(Session::has(Config::get('form','token_name'))) {
			// get it
			$current_token = Session::get(Config::get('form','token_name'));
			// if it matches
			if($current_token === $token) {
				// remove it
				Session::remove(Config::get('form','token_name'));
				// return true
				return true;
			}
			else {
				// not valid
				return false;
			}
		}
		// invalid token
		else {
			// return false
			return false;
		}

	}
	
}

?>
