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
 
class Request {
	
	private static $_url;
	private static $_get;
	private static $_post;
	private static $_files;
	private static $_cookie;
	private static $_argv;
	private static $_server;
	private static $_headers;
	private static $_context;
	private static $_method;
	private static $_protocol;
	private static $_port;
	private static $_signature;

	// available methods	
	const METHODS = [
		'put', 'post', 'get', 'delete', 'options', 'head'
	]; 

	public static function init() :void {

		// set proper context depending if we are in command line
		self::$_context = isset($_SERVER['argv'][0]) ? 'CLI' : 'HTTP';
		
		// set current URL depending on the context
		self::$_url = self::isCli() ? (isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '/') : $_SERVER['REQUEST_URI'];
		
		// set the request method, use "get" as a fallback
		self::$_method = isset($_SERVER['REQUEST_METHOD']) && in_array(strtolower($_SERVER['REQUEST_METHOD']), self::METHODS) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';

		// set the request protocol
		self::$_protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';

		// set the request port
		self::$_port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;

		// set the request signature with post, if any
		self::$_signature = self::isPost() ? sha1(self::$_url.json_encode($_POST)) : sha1(self::$_url);

		// set globals
		self::$_get		= isset($_GET)				? $_GET				: array();
		self::$_post	= isset($_POST)				? $_POST			: array();
		self::$_server	= isset($_SERVER)			? $_SERVER			: array();
		self::$_files	= isset($_FILES)			? $_FILES			: array();
		self::$_argv	= isset($_SERVER['argv'])	? $_SERVER['argv']	: array();

		// set the headers with a FPM fix
		function_exists('getallheaders') ? self::$_headers = getallheaders() : self::setHeaders();

		// remove globals
		unset($_GET, $_POST, $_SERVER, $_FILES);

	}
	
	public static function getUrl() :string {
		
		// get current url
		return self::$_url ?: '/';
		
	}

	public static function getMethod() :string {

		// get the current method (used by the Router for matching routes)
		return self::$_method;

	}
	
	public static function getSignature() :string {
		
		// return current signature
		return self::$_signature;
		
	}

	public static function getProtocol() :string {

		// return current protocol
		return self::$_protocol;

	}

	public static function getPort() :int {

		// return current port
		return (int) self::$_port;

	}
	
	private static function setHeaders() :void {
	
		// for each $_server key
		foreach(self::$_server as $name => $value) { 
			// if it's a header
			if(substr($name, 0, 5) == 'HTTP_') { 
				// clean it
				self::$_headers[str_replace(' ','-',ucwords(strtolower(str_replace('_',' ',substr($name,5)))))] = $value; 
			} 
		} 
		
	}
	
	public static function setUrlParameter(string $key, $value=null) :void {
		
		// set the value
		self::$_get[$key] = $value;
		
	}
	
	/**
	 * Get a single Header variable.
	 *
	 * @access public
	 * @param  string $variable The variable we wish to return.
	 * @param  mixed  $default  If the variable is not found, this is returned.
	 * @return mixed
	 * @static
	 */
	public static function header(string $variable, $default=null) {
		return isset(self::$_headers[$variable])
			? self::$_headers[$variable]
			: $default;
	}

	
	/**
	 * Get a single GET variable.
	 *
	 * @access public
	 * @param  string $variable The variable we wish to return.
	 * @param  mixed  $default  If the variable is not found, this is returned.
	 * @return mixed
	 * @static
	 */
	public static function get(string $variable, $default = null) {
		return isset(self::$_get[$variable])
			? urldecode(self::$_get[$variable])
			: $default;
	}

	/**
	 * Get a single POST variable.
	 *
	 * @access public
	 * @param  string $variable The variable we wish to return.
	 * @param  mixed  $default  If the variable is not found, this is returned.
	 * @return mixed
	 * @static
	 */
	public static function post(string $variable, $default = null) {
		return isset(self::$_post[$variable])
			? self::$_post[$variable]
			: $default;
	}

	/**
	 * Get a single FILES variable.
	 *
	 * @access public
	 * @param  string $variable The variable we wish to return.
	 * @param  mixed  $default  If the variable is not found, this is returned.
	 * @return mixed
	 * @static
	 */
	public static function files(string $variable, $default = null) {
		return isset(self::$_files[$variable])
			? self::$_files[$variable]
			: $default;
	}

	/**
	 * Get a single SERVER variable.
	 *
	 * @access public
	 * @param  string $variable The variable we wish to return.
	 * @param  mixed  $default  If the variable is not found, this is returned.
	 * @return mixed
	 * @static
	 */
	public static function server(string $variable, $default = null) {
		return isset(self::$_server[$variable])
			? self::$_server[$variable]
			: $default;
	}

	
	/**
	 * Get a single argv variable.
	 *
	 * @access public
	 * @param  string $variable The variable we wish to return.
	 * @param  mixed  $default  If the variable is not found, this is returned.
	 * @return mixed
	 * @static
	 */
	public static function argv(string $variable, $default = null) {
		return isset(self::$_argv[$variable])
			? self::$_argv[$variable]
			: $default;
	}

	/**
	 * Check if the request is done in command line interface.
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isCli() :bool {
		
		// if method is post return true
		return self::$_context == 'CLI';
		
	}	

	/**
	 * Check whether the users request was a standard request, or via Ajax.
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isAjax() :bool {
		return isset(self::$_server['HTTP_X_REQUESTED_WITH'])
			&& strtolower(self::$_server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	/**
	 * Check if the request is a GET.
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isGet() :bool {
		
		// if method is post return true
		return self::$_method == 'get';
		
	}

	/**
	 * Check if the request is a POST.
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isPost() :bool {
		
		// if method is post return true
		return self::$_method == 'post';
		
	}

	/**
	 * Check if the request is a DELETE.
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isDelete() :bool {
		
		// if method is post return true
		return self::$_method == 'delete';
		
	}

	/**
	 * Check if the request is a PUT.
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isPut() :bool {
		
		// if method is post return true
		return self::$_method == 'put';
		
	}

	/**
	 * Check if the request is secured.
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isSecure() :bool {
		
		// if method is post return true
		return self::$_protocol == 'https';
		
	}	
	
}

?>