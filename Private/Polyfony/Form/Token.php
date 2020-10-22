<?php

namespace Polyfony\Form;
use \Polyfony\{Config, Request, Store\Session, Hashs, Element, Response}; 


class Token extends Integrity {

	// the actual value of the token
	private ?string $value 	= null;

	// this will instanciate a token
	public function __construct() {

		// instancing a token will disable browser and framework caching
		// as to prevent mismatching tokens
		Response::disableBrowserCache();
		Response::disableOutputCache();

		// a unique token will be generated, and will live in the PHP session
		$this->value = strtolower(Hashs::get(
			Config::get('form','token_name') . uniqid(false,true)
		));

		// store it in the current session
		Integrity::putInSession(
			Config::get('form','token_name'), 
			$this->value
		);

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
			[
				'missing'=>'Polyfony/Form/Token::enforce() missing Token (CSRF)',
				'invalid'=>'Polyfony/Form/Token::enforce() invalid Token (CSRF)'
			], 
			$prevent_redirection
		);
	}
	
}

?>
