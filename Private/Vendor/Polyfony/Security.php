<?php
/**
 * PHP Version 5
 * This security class support three levels of authentication
 * The first one is just being logged in, the second one is having a minimal numerical level
 * The third one is having a module, than can be bypassed by a certain level.
 * Authentication uses a cookie to store the session key, this session key is associated with
 * The user agent and the IP, so that stealing the cookie will result in the closing of the session.
 * The default hash algorithm is sha512 and a salt is used.
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Security {
	
	// default is not granted
	protected static $_granted = false;
	protected static $_account = null;
	
	// main authentication method that will authenticated and optionnaly apply a module/level rule
	public static function enforce($module=null, $level=null) {
		
		// if we have a security cookie we authenticate with it
		!Store\Cookie::has(Config::get('security','cookie')) ?: self::authenticate();
		
		// if we have a post and posted a login, we log in with it
		!Request::post(Config::get('security','login')) ?: self::login();

		// if there is a module required and we have it, allow access
		!$module ?: self::$_granted = self::hasModule($module);

		// if a level is required and we have it, allow access
		!($level && !self::$_granted) ?: self::$_granted = self::hasLevel($level);

		// and now we check if we are granted access
		self::$_granted ?: self::refuse('You do not have sufficient permissions', 403, false, false);
				
	}
	
	// authenticate then close the session
	public static function disconnect() {

		// first authenticate
		self::enforce();

		// then close the session
		self::$_account
			->set('session_key','')
			->set('session_expiration_date','')
			->save();

		// remove the cookie
		Store\Cookie::remove(Config::get('security', 'cookie'));

		// and redirect to the exit route or fallback to the login route
		Response::setRedirect(Config::get('router', 'exit_route') ?: Config::get('router', 'login_route'));

		// render the response
		Response::render();

	}

	// internal authentication method that will grant access based on an existing session
	private static function authenticate() {

		// if we did not authenticate before
		if(!self::$_account) {

			// search for an account with that session key
			$account = Database::query()
				->select()
				->first()
				->from('Accounts')
				->where(array('session_key'=>Store\Cookie::get(Config::get('security', 'cookie'))))
				->whereHigherThan('session_expiration_date', time())
				->execute();

			// if no matching session is found we remove the cookie
			$account ?: self::refuse('Your session is no longer valid', 403, true);

			// check if the store session key matches the dynamically generated one
			self::match($account) ?: self::refuse('Your signature has changed, please log-in again', 403, true);

			// check account expiration
			!($account->get('account_expiration_date') && time() > $account->get('account_expiration_date', true)) ?:
				self::refuse('Your account has expired', 403, true);

			// update our credentials
			self::$_account = $account;

			// set access as granted
			self::$_granted = true;

		}
		
	}
	
	// internal login method that will open a session
	private static function login() {
		
		// look for users with this login
		$account = Database::query()
			->select()
			->from('Accounts')
			->where(array(
				'login'		=> Request::post(Config::get('security', 'login')),
				'is_enabled'=> 1
			))
			->first()
			->execute();
			
		// user is found
		if($account) {
			// if the account has been forced by the same ip recently
			if(
				$account->get('last_failure_origin') == Request::server('REMOTE_ADDR') &&
				$account->get('last_failure_date', true) > time() - Config::get('security', 'waiting_duration')
			) {
				// extend the lock on the account
				$account
					->set('last_failure_date', time())
					->set('last_failure_agent', Request::server('HTTP_USER_AGENT'))
					->save();
				// log the action
				Logger::warning("User {$account->get('login')} is being blocked");
				// refuse access
				self::refuse('Please wait ' . Config::get('security', 'waiting_duration') . ' seconds before trying again');
			}
			
			// if the account has expired
			if($account->get('account_expiration_date') && time() > $account->get('account_expiration_date',true)) {
				// refuse access
				self::refuse('Your account was only valid until '.$account->get('account_expiration_date'));	
			}

			// if the password matches
			if($account->get('password') === self::getPassword(Request::post(Config::get('security', 'password')))) {

				// generate the expiration date
				$session_expiration = time() + ( Config::get('security', 'session_duration') * 3600 );
				
				// generate a session key with its expiration, the login, the password, the ip, the user agent
				$session_signature = self::getSignature(
					$account->get('login') . $account->get('password') . $session_expiration);

				// store a cookie with our current session key in it
				$cookie_creation = Store\Cookie::put(
					Config::get('security', 'cookie'), 
					$session_signature, 
					true, 
					Config::get('security', 'session_duration')
				);
				
				// if the cookie creation failed
				$cookie_creation ?: self::refuse('You must accept cookies to log in');
				
				// update the account
				$account
					->set('session_expiration_date',$session_expiration)	
					->set('session_key',			$session_signature)
					->set('last_login_origin',		Request::server('REMOTE_ADDR'))
					->set('last_login_agent',		Request::server('HTTP_USER_AGENT'))
					->set('last_login_date',		time())
					->save();

				// update our credentials
				self::$_account = $account;

				// allow the basic authentication
				self::$_granted = true;

				// log the loggin action
				Logger::info('User has logged in');
				
			}
			// passwords dont match
			else {
				// update the account
				$account
					->set('last_failure_agent',	Request::server('HTTP_USER_AGENT'))
					->set('last_failure_origin',Request::server('REMOTE_ADDR'))
					->set('last_failure_date',	time())
					->save();
				// refuse access
				self::refuse('Wrong password');
			}
		}
		// user does not exist
		else { self::refuse('Account does not exist or is disabled'); }
	}

	// internal method to refuse access
	private static function refuse($message='Forbidden', $code='403', $logout=false, $redirect=true) {
		// remove any existing session cookie
		!$logout ?: Store\Cookie::remove(Config::get('security','cookie'));
		// we will redirect to the login page
		!$redirect ?: Response::setRedirect(Config::get('router','login_route'), 3);
		// trhow a polyfony exception that by itself will stop the execution with maybe a nice exception handler
		Throw new Exception($message, $code);
	}

	// this will check that the opened session matches the current client's signature
	private static function match($account) {
		// get the session key existing in the database
		$existing_key = $account->get('session_key');
		// generate a new session key for this request
		$dynamic_key = self::getSignature(
			$account->get('login') . $account->get('password') . 
			$account->get('session_expiration_date', true)
		);
		// return true only if they match
		return($existing_key === $dynamic_key ? true : false);

	}
	
	// internal method for generating unique signatures
	private static function getSignature($mixed) {
		
		// compute a hash with (the provided string + salt + user agent + remote ip)
		return(hash(Config::get('security','algo'), 
			Request::server('HTTP_USER_AGENT') . Request::server('REMOTE_ADDR') . 
			Config::get('security','salt') . is_string($mixed) ? $mixed : json_encode($mixed)
		));
		
	}

	// generate the hash for a specific password (useful for creating users)
	public static function getPassword($string) {
		
		// get a signature using (the provided string + salt)
		return(hash(Config::get('security','algo'),
			Config::get('security','salt') . $string . Config::get('security','salt')
		));
		
	}
	
	// manually check for a specific level
	public static function hasLevel($level=null) {
	
		// if we have said level
		return(self::get('id_level', 100) <= $level ? true : false);
		
	}
	
	// manually check for a specific module
	public static function hasModule($module=null) {
		
		// if module is in our credentials
		return(in_array($module, self::get('modules_array', array())) ?: false);
		
	}

	// check if the user has been authenticated
	public static function isAuthenticated() {
		// return the current status
		return self::$_granted;
	}
	
	// get a specific credential
	public static function get($credential, $default=null) {

		// return said credential or default if not authenticated or credential does not exist
		return(
			self::$_account && self::$_account->get($credential) ? 
			self::$_account->get($credential) : $default
		);
		
	}
	
}	

?>
