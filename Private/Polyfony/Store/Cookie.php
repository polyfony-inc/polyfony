<?php
/**
 * Stores data within the users own cookie store.
 *
 * @author    Christopher Hill <cjhill@gmail.com> modified by AnnoyingTechnology
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
	public static function has(
		string $variable
	) :bool {
		return 
			isset($_COOKIE[$variable]) && 
			strlen($_COOKIE[$variable]);
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
	public static function put(
		string $variable, 
		$value, 
		bool $overwrite = false,
		?int $lifetime = null
	) :bool {
		// If it exists, and we do not want to overwrite
		if (self::has($variable) && !$overwrite) {
			// then throw exception
			Throw new \Polyfony\Exception(
				"{$variable} already exists in the store.",
				400
			);
		}
		// if a lifetime is set convert it to seconds in the future, or use a default of 24 hours
		$lifetime = $lifetime ? 
			time() + $lifetime * 3600 : 
			time() + 24 * 3600;
		// encode and compress the value
		$value = gzcompress(json_encode($value));
		// actually set the cookie
		setcookie($variable, $value, $lifetime, '/');
		// set it manually into the superglobal
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
	public static function get(
		string $variable, 
		bool $raw = false
	) {
		// If it exists, and we do not want to overwrite
		if (!self::has($variable)) {
			// then throw exception
			Throw new \Polyfony\Exception(
				"{$variable} does not exist in the store.",
				404
			);
		}

		return $raw ? 
			$_COOKIE[$variable] : 
			json_decode(gzuncompress($_COOKIE[$variable]));

	}

	/**
	 * Remove the variable in the store.
	 *
	 * @access public
	 * @param  string $variable The name of the variable to remove.
	 * @throws Exception        If the variable does not exist.
	 * @static
	 */
	public static function remove(
		string $variable
	) :bool {
		// If it exists, and we do not want to overwrite, then throw exception
		if (!self::has($variable)) {
			Throw new \Polyfony\Exception(
				"{$variable} does not exist in the store.", 
				404
			);
		}
		// Remove the cookie by setting its expires in the past
		return setcookie($variable, '', time() - 3600, '/');
	}
}

?>
