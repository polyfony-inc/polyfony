<?php

namespace Polyfony\Security;
use Polyfony\Config as Conf;
use Polyfony\Security as Sec;
use Polyfony\Logger as Log;
use Polyfony\Store\Cookie as Cook;

class Accounts extends \Polyfony\Record {

	// this methods should be moved to Models\Accounts but that would break backward compatiblity
	public static function getFirstEnabledWithLogin(string $posted_login) {
		return \Models\Accounts::_select()
			->first()
			->where([
				'login'		=> $posted_login,
				'is_enabled'=> 1
			])
			->execute();
	}

	public static function getFirstEnabledWithNonExpiredSession(string $session_key) {
		return \Models\Accounts::_select()
			->first()
			->where([
				'session_key'	=>$session_key,
				'is_enabled'	=>1
			])
			->whereHigherThan([
				'session_expiration_date'=>time()
			])
			->execute();
	}

	public function closeSession() :bool {

		// remove the session from the database, and remove the session cookie
		return 
			$this->set(['session_key'=>'','session_expiration_date'=>''])->save() && 
			Cook::remove(Conf::get('security', 'cookie'));
			
	}

	// this methods should be moved to Models\Accounts but that would break backward compatiblity
	public function hasFailedLoginAttemptsRecentFrom(string $remote_address) :bool {
		return 
			$this->get('last_failure_origin') == $remote_address &&
			$this->get('last_failure_date', true) > time() - Conf::get('security', 'waiting_duration');
	}
	
	public function hasMatchingDynamicKey() :bool {

		// return true if the static session key matches the dynamically generated one
		// this ensures that the session key has not been moved to another computer
		// they won't match if the remote address, or the user agent has changed
		return Sec::getSignature(
				$this->get('login') . $this->get('password') . $this->get('session_expiration_date', true)
			) === $this->get('session_key');

	}

	// this methods should be moved to Models\Accounts but that would break backward compatiblity
	public function extendLoginBan() :void {
		// log the action
		Log::warning("Account {$this->get('login')} got its ban period extended due to wrong password");
		// updathe the account
		$this->set([
			'last_failure_date'		=>time(),
			'last_failure_agent'	=>Sec::getSafeUserAgent()
		])->save();
	}

	// this methods should be moved to Models\Accounts but that would break backward compatiblity
	public function hasItsValidityExpired() :bool {
		return 
			$this->get('account_expiration_date') && 
			time() > $this->get('account_expiration_date',true);
	}


	public function tryOpeningSession() {

		// generate the expiration date
		$session_expiration = time() + ( Conf::get('security', 'session_duration') * 3600 );
		
		// generate a session key with its expiration, the login, the password, the ip, the user agent
		$session_signature = Sec::getSignature($this->get('login').$this->get('password').$session_expiration);

		// if we manage to open the session properly
		return 
			// if the cookie creation went right
			$this->createCookieSession($session_signature) && 
			// and the account record updating went right too
			$this->createDatabaseSessionUntil($session_expiration, $session_signature);

	}

	// first part of the session opening process
	private function createCookieSession(string $session_signature) :bool {

		// store a cookie with our current session key in it
		return Cook::put(
			Conf::get('security', 'cookie'), 
			$session_signature, 
			true, 
			Conf::get('security', 'session_duration')
		);

	}

	// second part of the session opening process
	private function createDatabaseSessionUntil(int $expiration_date, string $session_signature) :bool {

		// open the session
		return $this->set([
			'session_expiration_date'	=> $expiration_date,
			'session_key'				=> $session_signature,
			'last_login_origin'			=> Sec::getSafeRemoteAddress(),
			'last_login_agent'			=> Sec::getSafeUserAgent(),
			'last_login_date'			=> time()
		])->save();

	}

	public function registerFailedLoginAttemptFrom(string $remote_address, string $user_agent) :void {

		// log the failed login
		Log::warning("Account {$this->get('login')} has tried to log-in with a wrong password");
		// save the incident to prevent bruteforce attacks
		$this->set([
			'last_failure_agent'	=>$user_agent,
			'last_failure_origin'	=>$remote_address,
			'last_failure_date'		=>time()
		])->save();

	}

	public function hasThisPassword(string $uncertain_password) :bool {
		// compare the existing signature, with the signature of the password to check
		return $this->get('password') === Sec::getPassword($uncertain_password);

	}


}

?>
