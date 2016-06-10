<?php
/**
 * Stores data within the users own cookie store.
 *
 * @copyright Copyright (c) 2012-2013 Christopher Hill
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author    Christopher Hill <cjhill@gmail.com>
 * @package   MVC
 */

namespace Polyfony\Store;

class Cookie implements StoreInterface {
	/**
	 * Check whether the variable exists in the store.
	 *
	 * @access public
	 * @param  string  $variable The name of the variable to check existence of.
	 * @return boolean           If the variable exists or not.
	 * @static
	 */
	public static function has($variable) {
		return isset($_COOKIE[$variable]);
	}

	/**
	 * Store a variable for use.
	 *
	 * @access public
	 * @param  string  $variable  The name of the variable to store.
	 * @param  mixed   $value     The data we wish to store.
	 * @param  int     $expires   How many seconds the cookie should be kept.
	 * @param  boolean $overwrite Whether we are allowed to overwrite the variable.
	 * @return boolean            If we managed to store the variable.
	 * @throws Exception          If the variable already exists when we try not to overwrite it.
	 * @static
	 */
	public static function put($variable, $value, $overwrite = false, $lifetime = null) {
		// If it exists, and we do not want to overwrite, then throw exception
		if (self::has($variable) && ! $overwrite) {
			throw new \Polyfony\Exception("{$variable} already exists in the store.");
		}
		// if a lifetime is set convert it to seconds in the future, or use a default of 24 hours
		$lifetime = $lifetime ? time() + $lifetime * 3600 : 24 * 3600;
		// encode and compress the value
		$value = gzcompress(json_encode($value));
		// actually set the cookie
		setcookie($variable, $value, $lifetime, '/');
		// set it manually into the supergloba
		$_COOKIE[$variable] = $value;
		// return its presence
		return self::has($variable);
	}

	/**
	 * Return the variable's value from the store.
	 *
	 * @access public
	 * @param  string $variable The name of the variable in the store.
	 * @return mixed
	 * @throws Exception        If the variable does not exist.
	 * @static
	 */
	public static function get($variable) {
		// If it exists, and we do not want to overwrite, then throw exception
		if (! self::has($variable)) {
			throw new \Polyfony\Exception("{$variable} does not exist in the store.");
		}

		return json_decode(gzuncompress($_COOKIE[$variable]));
	}

	/**
	 * Remove the variable in the store.
	 *
	 * @access public
	 * @param  string $variable The name of the variable to remove.
	 * @throws Exception        If the variable does not exist.
	 * @static
	 */
	public static function remove($variable) {
		// If it exists, and we do not want to overwrite, then throw exception
		if (! self::has($variable)) {
			throw new \Polyfony\Exception("{$variable} does not exist in the store.");
		}
		// Remove the cookie by setting its expires in the past
		setcookie($variable, '', time() - 3600, '/');
	}
}

?>
