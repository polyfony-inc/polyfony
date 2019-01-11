<?php

/**
 * This security class support three levels of authentication
 * The first one is just being logged in, the second one is having a minimal numerical level
 * The third one is having a module, than can be bypassed by a certain level.
 * Authentication uses a cookie to store the session key, this session key is associated with
 * The user agent and the IP, so that stealing the cookie will result in the closing of the session.
 * The default hash algorithm is sha512 and a salt is used.
 */

namespace Polyfony;
use Polyfony\Store\Cookie as Cook;
use Polyfony\Store\Session as Session;

class Security {

	// default is not granted
	protected static $_granted = false;
	protected static $_account = null;
	
	// main authentication method that will authenticate and optionnaly apply a module/level rule
	public static function enforce(string $module=null, int $level=null) :void {
		
		// if we have a security cookie we authenticate with it
		!Cook::has(Config::get('security','cookie')) ?: self::authenticate();
		
		// if we have a post and posted a login, we log in with it
		!Request::post(Config::get('security','login')) ?: self::login();

		// if there is a module required and we have it, allow access
		!$module ?: self::$_granted = self::hasModule($module);

		// if a level is required and we have it, allow access
		!($level && !self::$_granted) ?: self::$_granted = self::hasLevel($level);

		// and now we check if we are granted access
		self::$_granted ?: 
			self::refuse(
				(!$module && !$level ? 
					'You are not authenticated' :  
					'You do not have sufficient permissions'
				), 403, false, true
			);
				
	}
	
	// authenticate then close the session
	public static function disconnect() :void {

		// first authenticate
		self::enforce();

		// then close the session
		self::$_account->closeSession();

		// and redirect to the exit route or fallback to the login route
		Response::setRedirect(Config::get('router', 'exit_route') ?: Config::get('router', 'login_route'));

		// render the response
		Response::render();

	}

	// internal authentication method that will grant access based on an existing session
	protected static function authenticate() :void {
		// if we did not authenticate before
		if(!self::$_account) {
			// search for an enabled account with that session key and a non expired session
			$account = \Models\Accounts::getFirstEnabledWithNonExpiredSession(
				// the session key
				Cook::get(Config::get('security', 'cookie'))
			);
			// if the cookie session key doesn't match any existing account session, we deny access
			$account ?: 
				self::refuse(
					'Your session is no longer valid', 
					403, true);
			// if the stored key and dynamically generated mismatch, we deny access
			$account->hasMatchingDynamicKey() ?: 
				self::refuse(
					'Your signature has changed since your last visit, please log-in again', 
					403, true);
			// if account has expired, we deny access
			!$account->hasItsValidityExpired() ?:
				self::refuse(
					"Your account has expired, it was valid until {$account->get('account_expiration_date')}", 
					403, true);
			// update our credentials
			self::$_account = $account;
			// set access as granted
			self::$_granted = true;
		}	
	}

	// internal login method that will open a session
	protected static function login() :void {
		
		// look for users with this login
		$account = \Models\Accounts::getFirstEnabledWithLogin(
			Request::post( Config::get('security', 'login') )
		);
		// if the account does not exist/is not found
		if(!$account) {
			// we deny access
			self::refuse('Account does not exist or is disabled'); 
		}
		// if the account has been forced by that ip recently
		if($account->hasFailedLoginAttemptsRecentFrom(self::getSafeRemoteAddress())) {
			// extend the lock on the account
			$account->extendLoginBan();
			// refuse access
			self::refuse('Please wait '.Config::get('security', 'waiting_duration').' seconds before trying again');
		}
		// if the account has expired
		if($account->hasItsValidityExpired()) {
			// register that failure
			$account->registerFailedLoginAttemptFrom(self::getSafeRemoteAddress(), self::getSafeUserAgent());
			// refuse access
			self::refuse("Your account has expired, it was valid until {$account->get('account_expiration_date')}");	
		}
		// if the posted password doesn't match the account's password
		if(!$account->hasThisPassword(Request::post(Config::get('security', 'password')))) {
			// register that failure
			$account->registerFailedLoginAttemptFrom(self::getSafeRemoteAddress(), self::getSafeUserAgent());
			// refuse access
			self::refuse('Wrong password');
		}
		// if we failed to open a session
		if(!$account->tryOpeningSession()) {
			// we report that something is wrong
			self::refuse('Your session failed to open, make sure your browser accepts cookies', 500);
		}
		// then session has been opened
		else {
			// log the loggin action
			Logger::info("Account {$account->get('login')} has logged in");
			// put the user inside of us
			self::$_account = $account;
			// set the most basic authentication block as being true/passed
			self::$_granted = true;
			// if we have an url in the session, redirect to it (and remove it)
			!Session::has('previously_requested_url') ?: self::redirectToThePreviouslyRequestedUrl();
		}
		
	}

	// internal method to refuse access
	protected static function refuse(
		string $message='Forbidden', int $code=403, bool $logout=false, bool $redirect=true
	) :void {
		// remove any existing session cookie
		!$logout ?: Cook::remove(Config::get('security','cookie'));
		// we will redirect to the login page
		!$redirect ?: Response::setRedirect(Config::get('router','login_route'), 3);
		// save the desired url for further redirection later on
		Session::put('previously_requested_url', Request::getUrl()); 
		// trhow a polyfony exception that by itself will stop the execution with maybe a nice exception handler
		Throw new Exception($message, $code);
	}

	protected static function redirectToThePreviouslyRequestedUrl() :void {
		// define the redirection
		Response::setRedirect(Session::get('previously_requested_url'));
		// remove the temporary url
		Session::remove('previously_requested_url');
	}
	
	// internal method for generating unique signatures
	public static function getSignature($mixed) :string {
		// compute a hash with (the provided string + salt + user agent + remote ip)
		return(hash(Config::get('security','algo'), 
			self::getSafeUserAgent() . self::getSafeRemoteAddress() . 
			Config::get('security','salt') . is_string($mixed) ? $mixed : json_encode($mixed)
		));
	}

	// generate the hash for a specific password (useful for creating users)
	public static function getPassword(string $string) :string {
		// get a signature using (the provided string + salt)
		return(hash(Config::get('security','algo'),
			Config::get('security','salt') . $string . Config::get('security','salt')
		));
	}

	// return the account
	public static function getAccount() :\Models\Accounts {
		return self::$_account;
	}
	
	// OLD SHORTCUT for Security->getAccount->hasLevel()
	public static function hasLevel(int $level=null) :bool {
		// if we have said level
		return self::$_account ? 
			self::getAccount()->hasLevel($level) : false;
	}
	
	// OLD SHORTCUT for Security->getAccount->hasModule()
	public static function hasModule(string $module=null) :bool {
		// if module is in our credentials
		return self::$_account ? 
			self::getAccount()->hasModule($module) : false;
	}

	// OLD SHORTCUT for Security->getAccount->get($something)
	public static function get(string $credential, $default=null) {
		// return said credential or default if not authenticated or credential does not exist
		return(
			self::$_account && self::$_account->get($credential) ? 
			self::$_account->get($credential) : $default
		);
	}

	// check if the user has been authenticated
	// this method's name might be ambiguous, it's more of an isGranted()
	public static function isAuthenticated() :bool {
		// return the current status
		return self::$_granted;
	}

	// return the user agent truncate to prevent database filling by huge faked user agent
	public static function getSafeUserAgent() {
		return Format::truncate(Request::server('HTTP_USER_AGENT'), 512);
	}

	public static function getSafeRemoteAddress() {
		return Format::truncate(Request::server('REMOTE_ADDR'), 32);
	}


}	

?>
