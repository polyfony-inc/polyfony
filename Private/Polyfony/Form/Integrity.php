<?php

namespace Polyfony\Form;
use Polyfony\Request as Request;
use \Polyfony\Response as Response;
use \Polyfony\Store\Session as Session;

class Integrity { 

	// number of captchas and tokens to keep in the session
	const maximum_concurrent_session_items = 10;

	protected static $_errors = [
		'missing'=>'',
		'invalid'=>''
	];

	// this will check, upon posting the form, that it is legitimate
	protected static function enforceFor(
		string $variable_name, 
		array $error_messages,
		bool $prevent_redirection = false, 
	) :void {

		// if the request is of type post
		if(Request::isPost()) {
			// is a token is present
			if(Request::post($variable_name)) {
				// check the provided token against our legitimate tokens
				if(!self::isLegitimate($variable_name)) {
					// throw an exception to prevent this action from succeeding
					self::throwExceptionBecause(
						$error_messages['invalid'], 
						$prevent_redirection
					);
				}
			}
			// missing token
			else {
				// throw an exception to prevent this action from succeeding
				self::throwExceptionBecause(
					$error_messages['missing'], 
					$prevent_redirection
				);
			}
		}

	}

	private static function isLegitimate(string $variable_name) :bool {

		// look for that token_or_captcha in the current session
		if(Session::has($variable_name)) {
			// if it matches
			if(self::existsInSession(
					$variable_name, 
					Request::post($variable_name)
			)) {
				// remove it
				self::removeFromSession(
					$variable_name, 
					Request::post($variable_name)
				);
				// return true
				return true;
			}
			else {
				// log the failure
				\Polyfony\Logger::warning(
					'Form/Integrity check failed for '.$variable_name. 
					' ('.Request::post($variable_name).' provided and '.
					Session::get($variable_name).' was expected)'
				);
				// not valid
				return false;
			}
		}
		// invalid token_or_captcha
		else {
			// return false
			return false;
		}

	}

	protected static function throwExceptionBecause(
		string $error_message, 
		bool $prevent_redirection
	) {
		// soft redirect to the previous page after a few seconds
		$prevent_redirection ?: Response::setRedirect(Request::server('HTTP_REFERER'), 3);
		// throw an exception to prevent this action from succeeding
		Throw new \Polyfony\Exception($error_message, 403);
	}

	// place a captcha or token in an array (stored in the session)
	protected static function putInSession(
		string $variable_name, 
		string $variable_value
	) :bool {

		// if a list of variable is already available
		if(Session::has($variable_name)) {
			// get the currently available variables
			$list_of_variables = Session::get($variable_name);
			// complete it
			$list_of_variables[] = $variable_value;
			// put it back in
			return Session::put(
				$variable_name, 
				array_slice(
					$list_of_variables, 
					-1 * self::maximum_concurrent_session_items,
					self::maximum_concurrent_session_items
				), 
				true
			);
		}
		else {
			// create a brand new one
			return Session::put($variable_name, [$variable_value]);
		}

	}

	// remove a captcha or token from an array (stored in the session)
	protected static function removeFromSession(
		string $variable_name, 
		string $variable_value
	) :bool {

		// get all available variables
		$list_of_variables = Session::get($variable_name);

		// remove this specific variable from the list 
		unset(
			$list_of_variables[
				array_search(
					$variable_value,
					$list_of_variables
				)
			]
		);

		// put back the updated list in the session
		return Session::put(
			$variable_name, 
			$list_of_variables, 
			true
		);

	} 

	// checks if a captcha or token exists in an array (stored in the session)
	protected static function existsInSession(
		string $variable_name, 
		string $variable_value
	) :bool {
		// if the session has that variable
		if(Session::has($variable_name)) {
			// check the presence of the value in the array
			return in_array(
				$variable_value, 
				Session::get($variable_name)
			);
		}
		// the session variable does not even exist
		else {
			return false;
		}

	}

}

?>
