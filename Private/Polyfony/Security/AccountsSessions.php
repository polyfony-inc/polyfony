<?php

namespace Polyfony\Security;

// framework internals
use \Polyfony\{ 
	Security, 
	Config, 
	Logger, 
	Entity, 
	Store\Cookie as Cookie
};

// models
use Models\Accounts as Accounts;

class AccountsSessions extends Entity {

	public function getAccount() :Accounts {

		return new Accounts($this->get('id_account'));

	}

	public function hasExpired() :bool {

		return 
			time() > $this->get('is_expiring_on', true);

	}

	public function delete() :bool {

		// if a session cookie exists
		if(Cookie::has(Config::get('security', 'cookie'))) {
			// remove the associated cookie
			Cookie::remove(Config::get('security', 'cookie'));
		}
		// delete the session entity itself
		return parent::delete();
	}

	public function isSignatureConsistent(
		Accounts $account
	) :bool {
		// return true if the static session key matches the dynamically generated one
		// this ensures that the session key has not been moved to another computer
		// they won't match if the remote address, or the user agent has changed
		return Security::getSignature(
				$account->get('login', true) . 
				$account->get('password', true) . 
				$this->get('is_expiring_on', true)
			) === 
		$this->get('signature', true);
	}

	public static function removeExpired() :int {

		return self::_delete()
			->whereLessThan(['is_expiring_on'=>time()])
			->execute();

	}

}

?>
