<?php

namespace Polyfony;

use Polyfony\Locales as Locales;
use Polyfony\Store\Cookie as Cookie;
use Polyfony\Store\Session as Session;

class Security {

	// default is not authenticated
	protected static $_authenticated 	= false;
	protected static $_account 			= null;
	
	// main authentication method that will authenticate a user
	public static function authenticate() :void {

		// if we have a security cookie we authenticate with it
		!Cookie::has(Config::get('security','cookie')) ?: self::authenticateBySession();
		
		// if we have a post and posted a login, we log in with it
		!Request::post(Config::get('security','login')) ?: self::authenticateByForm();

		// and now we check if we are granted access
		self::$_authenticated ?: 
			self::deny(
				'You are not authenticated', 
				401, 
				false, 
				true
			);
	}
	
	public static function denyUnlessHasPermission(
		string $permission_name
	) :void {
		// the account lacks proper permission
		if(!self::$_account->hasPermission($permission_name)) {
			// it stops stop here
			self::deny(
				Locales::get('You do not have the') . ' '.
				Locales::get($permission_name). ' ' .
				Locales::get('permission')
			);
		}

	}

	// public static function denyUnlessHasAnyPermission(
	// 	array $permissions
	// ) :void {
		
	// }

	// public static function denyUnlessHasAllPermissions(
	// 	array $permissions
	// ) :void {

	// }

	public static function denyUnlessHasRole(
		string $role_name
	) :void {
		// the account lacks proper role
		if(!self::$_account->hasRole($role_name)) {
			// it stops stop here
			self::deny(
				Locales::get('You do not have the') . ' '.
				Locales::get($role_name). ' ' .
				Locales::get('role')
			);
		}

	}

	// authenticate then close the session
	public static function disconnect() :void {

		// first authenticate
		self::authenticate();

		// then close the session
		self::$_account->disconnect();

		// and redirect to the exit url or fallback to the login url
		Response::setRedirect(
			Config::get('router', 'exit_url') ?: 
				Config::get('router', 'login_url')
		);

		// render the response
		Response::render();

	}

	// internal authentication method that will grant access based on an existing session
	protected static function authenticateBySession() :void {
		// if we did not authenticate before
		if(!self::$_account) {
			// search for an enabled account with that session key and a non expired session
			$account = \Models\Accounts::getBySession(
				// the session key
				Cookie::get(Config::get('security', 'cookie'))
			);
			// if we cannot find a matching open session
			$account ?: 
				self::deny(
					'Your session is no longer valid, please log-in again', 
					401, true);
			// if we are supposed to check the signature consistency
			if(Config::get('security','enable_signature_verification'))	{
				// if the stored key and dynamically generated mismatch, we deny access
				$account
					->getSession()
					->isSignatureConsistent($account) ?: 
						self::deny(
							'Your device signature has changed since your last visit, please log-in again', 
							401, true);
			}
			// if account has expired, we deny access
			!$account->hasExpired() ?:
				self::deny(
					"Your account has expired, it was valid until {$account->get('is_expiring_on')}", 
					403, true);
			// update our credentials
			self::$_account = $account;
			// set security status as authenticated
			self::$_authenticated = true;
		}	
	}

	// internal login method that will open a session
	protected static function authenticateByForm() :void {
		
		// look for users with this login
		$account = \Models\Accounts::getByLogin(
			Request::post( Config::get('security', 'login') )
		);
		// if the account does not exist/is not found
		if(!$account) {
			// we deny access with 403 Status Code
			self::deny('Account does not exist is disabled'); 
		}
		// if the account has been forced by that ip recently
		if($account->isBeingForcedFrom(self::getSafeRemoteAddress())) {
			// register that failure
			$account->logFailedLogin('bruteforce');
			// refuse access with 403 Status Code
			self::deny(
				Locales::get('You have exceeded the maximum failed login attempts, please wait ').
				Format::duration(Config::get('security','forcing_timeframe')).' '.
				Locales::get('before trying again'),
				403
			);
		}
		// if the account has expired
		if($account->hasExpired()) {
			// register that failure
			$account->logFailedLogin('expired account');
			// refuse access
			self::deny(
				Locales::get('Your account has expired, it was valid until').' '.
				$account->get('is_expiring_on'),
				403
			);	
		}
		// if the posted password doesn't match the account's password
		if(!$account->hasThisPassword(
			Request::post(
				Config::get('security', 'password')
			)
		)) {
			// register that failure
			$account->logFailedLogin('wrong password');
			// refuse access with 403 Status Code
			self::deny('Wrong password', 403);
		}
		// if we failed to open a session
		if(!$account->login()) {
			// we report that something is wrong
			self::deny(
				'Your session failed to open, make sure your browser accepts cookies', 
				500
			);
		}
		// then session has been opened
		else {
			$account->logSuccessfulLogin();
			// put the user inside of us
			self::$_account = $account;
			// set the most basic authentication block as being true/passed
			self::$_authenticated = true;
			// if we have an url in the session, redirect to it (and remove it)
			!Session::has('previously_requested_url') ?: self::redirectToPreviousUrl();
		}
		
	}

	// internal method to refuse access
	protected static function deny(
		string  $message = 'Forbidden', 
		int 	$code = 403, 
		bool 	$logout = false, 
		bool 	$redirect = true
	) :void {
		// remove any existing session cookie
		!$logout ?: Cookie::remove(Config::get('security','cookie'));
		// we will redirect to the login page
		!$redirect ?: Response::setRedirect(Config::get('router','login_url'), (Request::isPost() ? 3 : 0));
		// save the desired url for further redirection later on
		Request::isAjax() || Request::isPost() ?: Session::put('previously_requested_url', Request::getUrl(), true); 
		// trhow a polyfony exception that by itself will stop the execution with maybe a nice exception handler
		Throw new Exception($message, $code);
	}

	protected static function redirectToPreviousUrl() :void {
		// define the redirection
		Response::setRedirect(Session::get('previously_requested_url'));
		// remove the temporary url
		Session::remove('previously_requested_url');
		// force render now (to prevent further directs)
		Response::render();
	}
	
	// internal method for generating unique signatures
	public static function getSignature(
		$mixed
	) :string {
		// compute a hash with (the provided string + salt + user agent + remote ip)
		return hash(Config::get('security','algo'), 
			self::getSafeUserAgent() . 
			self::getSafeRemoteAddress() . 
			Config::get('security','salt') . 
			(
				is_string($mixed) ? 
					$mixed : 
					json_encode($mixed)
			)
		);
	}

	// generate the hash for a specific password (useful for creating users)
	public static function getPassword(
		#[\SensitiveParameter]
		string $string
	) :string {
		// get a signature using (the provided string + salt)
		return hash(Config::get('security','algo'),
			Config::get('security','salt') . 
			$string . 
			Config::get('security','salt')
		);
	}

	// return the account
	public static function getAccount() :?\Models\Accounts {
		return self::$_account;
	}
	
	// check if the user has been authenticated
	// this method's name might be ambiguous, it's more of an isGranted()
	public static function isAuthenticated() :bool {
		// return the current status
		return self::$_authenticated;
	}

	// return the user agent truncate to prevent database filling by huge faked user agent
	public static function getSafeUserAgent() {
		return Format::truncate(
			Request::server('HTTP_USER_AGENT'), 
			512
		);
	}

	public static function getSafeRemoteAddress() {
		return Format::truncate(
			Request::server('REMOTE_ADDR'), 
			32
		);
	}	

}	

?>
