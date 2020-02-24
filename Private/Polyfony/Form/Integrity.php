<?php

namespace Polyfony\Form;
use Polyfony\Request as Request;
use \Polyfony\Response as Response;
use \Polyfony\Store\Session as Session;

class Integrity { 

	protected static $_errors = [
		'missing'=>'',
		'invalid'=>''
	];

	// this will check, upon posting the form, that it is legitimate
	protected static function enforceFor(
		string $variable, 
		bool $prevent_redirection = false, 
		array $error_messages
	) :void {

		// if the request is of type post
		if(Request::isPost()) {
			// is a token is present
			if(Request::post($variable)) {
				// check the provided token against our legitimate tokens
				if(!self::isLegitimate($variable)) {
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

	private static function isLegitimate(string $variable) :bool {

		// look for that token_or_captcha in the current session
		if(Session::has($variable)) {
			// if it matches
			if(
				(string) strtolower(Session::get($variable)) === 
				(string) strtolower(Request::post($variable))
			) {
				// remove it
				Session::remove($variable);
				// return true
				return true;
			}
			else {
				// log the failure
				\Polyfony\Logger::warning(
					'Form/Integrity check failed for '.$variable. 
					' ('.Request::post($variable).' provided and '.
					Session::get($variable).' was expected)'
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
		string $error_message, bool $prevent_redirection
	) {
		// soft redirect to the previous page after a few seconds
		$prevent_redirection ?: Response::setRedirect(Request::server('HTTP_REFERER'), 3);
		// throw an exception to prevent this action from succeeding
		Throw new \Polyfony\Exception($error_message, 400);
	}

}

?>
