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
 
class pfRequest {
	
	private static $_url;
	private static $_get;
	private static $_post;
	private static $_server;
	private static $_headers;
	private static $_context;
	private static $_method;
	private static $_signature;
	
	public static function init() {
		
		// set proper context
		self::setContext();
		// set globals
		self::setGlobals();
		// set headers
		self::setHeaders();
		// set proper url
		self::setUrl();
		// set the method
		self::setMethod();
		// set the current signature
		self::setSignature();
		// remove the globals
		self::removeGlobals();

	}
	
	public static function setContext() {
		
		// depending if we are in command line
		self::$_context = isset($_ARGV) ? 'CLI' : 'HTTP';
		
	}
	
	public static function getContext() {
		
		// return current context
		return(self::$_context);	
		
	}
	
	public static function setGlobals() {
	
		self::$_post	= isset($_POST) ?: array();
		self::$_get		= isset($_GET) ?: array();
		self::$_server	= isset($_SERVER) ?: array();
		
	}
	
	public static function setUrl() {
		
		// set current URL
		self::$_url = self::$_context == 'CLI' ? $_ARGV[2] : $_SERVER['REQUEST_URI'];

	}
	
	public static function getUrl() {
		
		// get current url
		return(self::$_url ?: '/');
		
	}

	public static function setSignature() {
		
		// compute the request signature
		self::$_signature = self::isPost() ? sha1(self::$_url.json_encode(self::$_post)) : sha1(self::$_url);
		
	}
	
	public static function getSignature() {
		
		// return current signature
		return(self::$_signature);
		
	}
	
	public static function setHeaders() {
	
		// if using FPM
		function_exists('getallheaders') ? self::$_headers = getallheaders() : self::getAllHeaders();
		
	}
	
	private static function getAllHeaders() {
	
		// for each $_server key
		foreach(self::$_server as $name => $value) { 
			// if it's a header
			if(substr($name, 0, 5) == 'HTTP_') { 
				// clean it
				self::$_headers[str_replace(' ','-',ucwords(strtolower(str_replace('_',' ',substr($name,5)))))] = $value; 
			} 
		} 
		
	}
	
	public static function getHeaders($key=null,$default=null) {
		
	}
	
	public static function setMethod() {
	
		// depending if superglobal post exists
		self::$_method = isset($_POST) ? 'get' : 'post';
		
	}
	
	public static function isGet() {
		
		// if method is get return true
		return(self::$_method == 'get' ? true : false);
		
	}
	
	public static function isPost() {
		
		// if method is post return true
		return(self::$_method == 'post' ? true : false);
		
	}	

	public static function removeGlobals() {
		
		// remove all superglobals
		unset(
			$_SERVER,
			$_POST,
			$_GET
		);
			
	}
	
}

?>