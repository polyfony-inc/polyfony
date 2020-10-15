<?php

namespace Polyfony;

class Hashs {

	// generate a hash of something
	public static function get($mixed = null) {
		// create a sha1 signature of the array with a salt
		$hash = hash(
			Config::get('hashs', 'algo'),
			json_encode([
				$mixed, 
				Config::get('hashs', 'salt')
			], JSON_NUMERIC_CHECK)
		);
		// divide the length by two
		$half_length = Config::get('hashs', 'length') / 2;
		// get last 10 and first 10 chars together, convert to uppercase, return the key
		return strtoupper(
			substr($hash, ($half_length * -1)) . 
			substr($hash, 0, $half_length)
		);
	}

	// compare an existing hash with a new dynamically generated one
	public static function compare(
		string $hash, 
		$mixed = null
	) {
		// if no hash is provided
		if(strlen($hash) != Config::get('hashs', 'length')) {
			// return false
			return false;	
		}
		// if keys do match
		return self::get($mixed) == $hash ?: false;
	}

}

?>
