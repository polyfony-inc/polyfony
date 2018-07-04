<?php

namespace Polyfony\Form;
use \Polyfony\Config as Config;
use \Polyfony\Request as Request;
use \Polyfony\Store\Session as Session;
use \Polyfony\Keys as Keys;
use \Polyfony\Element as Element;
use \Polyfony\Response as Response;

class Token extends Integrity {

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
	public static function enforce(bool $prevent_redirection = false) :void {
		// actually enforce using the inherited enforce method
		self::enforceFor(
			Config::get('form','token_name'), 
			$prevent_redirection, [
				'missing'=>'Polyfony/Form/Token::enforce() missing Token (CSRF)',
				'invalid'=>'Polyfony/Form/Token::enforce() invalid Token (CSRF)'
		]);
	}
	
}

?>
