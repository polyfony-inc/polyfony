<?php

namespace Polyfony\Store;

interface StoreInterface {
	
	
	/**
	 * Check whether the variable exists in the store.
	 *
	 * @access public
	 * @param  string  $variable The name of the variable to check existence of.
	 * @return boolean           If the variable exists or not.
	 */
	public static function has(
		string $variable
	) :bool;
	
	
	/**
	 * Store a variable for use.
	 *
	 * @access public
	 * @param  string  $variable  The name of the variable to store.
	 * @param  mixed   $value     The data we wish to store.
	 * @param  boolean $overwrite Whether we are allowed to overwrite the variable.
	 * @return boolean            If we managed to store the variable.
	 */
	public static function put(
		string $variable, 
		$value, 
		bool $overwrite = false,
		?int $lifetime = null
	) :bool;
	
	
	/**
	 * Return the variable's value from the store.
	 *
	 * @access public
	 * @param  string $variable The name of the variable in the store.
	 * @return mixed
	 */
	public static function get(
		string $variable
	);
	
	
	/**
	 * Remove the variable in the store.
	 *
	 * @access public
	 * @param  string $variable The name of the variable to remove.
	 * @return boolean          If the variable was removed successfully.
	 */
	public static function remove(
		string $variable
	) :bool;
}

?>
