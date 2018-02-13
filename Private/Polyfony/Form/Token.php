<?php
/**
 * PHP Version 5
 * String format helpers
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony\Form;

class Token {

	// the actual value of the token
	private $value 	= null;

	// this will instanciate a token
	public function __construct() {

		// a unique token will be generated, and will live in the PHP session
		$this->value = \Polyfony\Keys::generate(
			\Polyfony\Config::get('form','token_name') . uniqid(false,true)
		);

		// store it in the current session
		\Polyfony\Store\Session::put(\Polyfony\Config::get('form','token_name'), $this->value, true);

	}

	// get the value of the token, for manual use
	public function getValue() :string {

		// return as is
		return $this->value;

	}

	public function __toString() {

		// build a hidden input html element
		return (string) new \Polyfony\Element('input', [
			'type'	=>'hidden',
			'name'	=>\Polyfony\Config::get('form','token_name'),
			'value'	=>$this->value
		]); 

	}

	// this will check, upon posting the form, that it is legitimate
	public static function enforce() :void {

		// if the request is of type post
		if(\Polyfony\Request::isPost()) {
			// is a token is present
			if(\Polyfony\Request::post(\Polyfony\Config::get('form','token_name'))) {
				// check the provided token against our legitimate tokens
				if(!self::isLegitimate(\Polyfony\Request::post(\Polyfony\Config::get('form','token_name')))) {
					// soft redirect to the previous page after a few seconds
					\Polyfony\Response::setRedirect(\Polyfony\Request::server('HTTP_REFERER'), 3);
					// throw an exception to prevent this action from succeeding
					Throw new \Polyfony\Exception('Polyfony/Form/CSRF::enforce() invalid CSRF Token');
				}
			}
			// missing token
			else {
				// soft redirect to the previous page after a few seconds
				\Polyfony\Response::setRedirect(\Polyfony\Request::server('HTTP_REFERER'), 3);
				// throw an exception to prevent this action from succeeding
				Throw new \Polyfony\Exception('Polyfony/Form/CSRF::enforce() missing CSRF Token');
			}
		}

	}

	private static function isLegitimate($token) :bool {

		// look for that token in the current session
		if(\Polyfony\Store\Session::has(\Polyfony\Config::get('form','token_name'))) {
			// get it
			$current_token = \Polyfony\Store\Session::get(\Polyfony\Config::get('form','token_name'));
			// if it matches
			if($current_token === $token) {
				// remove it
				\Polyfony\Store\Session::remove(\Polyfony\Config::get('form','token_name'));
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
