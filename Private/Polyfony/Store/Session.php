<?php
namespace Polyfony\Store;

/**
 * Stores data for a user session.
 *
 * @author    Christopher Hill <cjhill@gmail.com>, modified by AnnoyingTechnology
 */
class Session implements StoreInterface
{

	/**
	 * Starts a PHP session is none is ready yet.
	 *
	 * @access private
	 * @return void
	 * @static
	 */
	private static function startSessionIfNeeded() :void {

		// if no session has been initiated yet
		if(session_status() == PHP_SESSION_NONE) {
			// we have to start one
			session_start();
		}

	}

	/**
	 * Check whether the variable exists in the store.
	 *
	 * @access public
	 * @param  string  $variable The name of the variable to check existence of.
	 * @return boolean           If the variable exists or not.
	 * @static
	 */
	public static function has($variable) {
		self::startSessionIfNeeded();
		return isset($_SESSION[$variable]);
	}

	/**
	 * Store a variable for use.
	 *
	 * @access public
	 * @param  string  $variable  The name of the variable to store.
	 * @param  mixed   $value     The data we wish to store.
	 * @param  boolean $overwrite Whether we are allowed to overwrite the variable.
	 * @return boolean            If we managed to store the variable.
	 * @throws Exception          If the variable already exists when we try not to overwrite it.
	 * @static
	 */
	public static function put($variable, $value, $overwrite = false) {
		self::startSessionIfNeeded();
		// If it exists, and we do not want to overwrite, then throw exception
		if (self::has($variable) && ! $overwrite) {
			throw new \Exception($variable . ' already exists in the store.');
		}

		$_SESSION[$variable] = serialize($value);
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
		self::startSessionIfNeeded();
		// If it exists, and we do not want to overwrite, then throw exception
		if (! self::has($variable)) {
			throw new \Exception("{$variable} does not exist in the store.");
		}

		return unserialize($_SESSION[$variable]);
	}

	/**
	 * Remove the variable in the store.
	 *
	 * @access public
	 * @param  string $variable The name of the variable to remove.
	 * @return boolean          If the variable was removed successfully.
	 * @throws Exception        If the variable does not exist.
	 * @static
	 */
	public static function remove($variable) {
		self::startSessionIfNeeded();
		// If it exists, and we do not want to overwrite, then throw exception
		if (! self::has($variable)) {
			throw new \Exception("{$variable} does not exist in the store.");
		}

		// Unset the variable
		unset($_SESSION[$variable]);

		// Was it removed
		return ! self::has($variable);
	}
}

?>
