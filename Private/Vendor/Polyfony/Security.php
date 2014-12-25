<?php
/**
 * PHP Version 5
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
	protected static $_credentials = array();
	
	public static function enforce($module=null,$level=null) {
		
		// if we have a security cookie we authenticate with it
		!Request::cookie(Config::get('security','cookie')) ?: self::authenticate();
		
		// if we have a post and posted a login, we log in with it
		!Request::post(Config::get('security','login')) ?: self::login();

		// if we have the module
		self::$_granted = ($module and self::hasModule($module)) ? true : false;

		// if we have the bypass level
		self::$_granted = (!self::$_granted and $level and self::hasLevel($level)) ? true : false;

		// and now we check if we have the proper rights
		self::$_granted ?: self::refuse();
				
	}
	
	private static function authenticate() {
		
	}
	
	private static function login() {
		
		// look for users with this login
		$found_accounts = Database::query()
			->select()
			->from('Accounts')
			->where(array(
				'login'=>Request::post(Config::get('security','login')),
				'is_enabled'=>'1'
			))
			->execute();

		// user is found
		if($found_accounts) {
			// if the password matches
			if($found_accounts[0]->get('password') == self::getPassword(Request::post(Config::get('security','password')))) {
				
				// allow 
				self::$_granted = true;
			}
			// passwords dont match
			else { self::refuse('Wrong password'); }
		}
		// user does not exist
		else { self::refuse('Account does not exist or is disabled'); }
	}
	
	public static function getPassword($string) {
		
		// get a signature using (the provided string + salt)
		return(hash(Config::get('security','algo'),
			Config::get('security','salt') . $string . Config::get('security','salt')
		));
		
	}
	
	public static function getSignature($string='') {
	
		// compute a hash with (the provided string + salt + user agent + remote ip)
		return(hash(Config::get('security','algo'), 
			Request::server('USER_AGENT') . Request::server('REMOTE_ADDR') . Config::get('security','salt') . $string
		));
		
	}
	
	public static function hasLevel($level=null) {
	
		// if we have said level
		return(self::get('level',100) <= $level ? true : false);
		
	}
	
	public static function hasModule($module=null) {
		
		// if module is in our credentials
		return(in_array($module,self::get('modules',array())) ?: false);
		
	}
	
	public static function get($credential,$default=null) {
		
		// return said credential or default
		return(isset(self::$_credentials[$credential]) ? self::$_credentials[$credential] : $default);
		
	}
	
	private static function refuse($message='Forbidden',$code='403') {
		// we will redirect to the login page
		Response::setRedirect(Config::get('router','login_route'),3);
		// trhow an exception
		Throw new Exception($message,$code);
	}
	
}	

?>