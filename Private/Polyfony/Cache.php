<?php

namespace Polyfony;

class Cache {

	private static function path($variable) :string {

		// secure the variable name
		$variable = \Polyfony\Format::fsSafe($variable);
		// build the path
		return Config::get('cache', 'path') . $variable;

	}

	public static function has(string $variable) :bool {

		// set the variable path
		$path = self::path($variable);
		// if the file exists 
		if(file_exists($path)) {
			// if it expired
			if(filemtime($path) < time()) {
				// the file expired, remove it
				unlink($path);
				// we don't have that element
				return false;
			}
			// the cache file has not expired yet
			else {
				// we do have the file
				return true;
			}
		}
		// file doesn't even exist
		else {
			return false;
		}

	}
	

	public static function put(string $variable, $value=null, $overwrite=false, $lifetime=false) :bool {
	
		// already exists and no overwrite
		if(self::has($variable) && !$overwrite) {
			// throw an exception
			Throw new \Polyfony\Exception("{$variable} already exists in the store.");
		}
		// store it
		file_put_contents(self::path($variable), msgpack_pack($value));
		// compute the expiration time or set to a year by default
		$lifetime = $lifetime ? time() + $lifetime : time() + 365 * 24 * 3600;
		// alter the modification time
		touch(self::path($variable), $lifetime);
		// return status
		return(self::has($variable));
		
	}
	

	public static function get(string $variable) {
		
		// doesn't exist in the store
		if(!self::has($variable)) {
			// throw an exception
			Throw new \Polyfony\Exception("{$variable} does not exist in the store.");
		}
		
		// return it
		return(msgpack_unpack(file_get_contents(self::path($variable))));
		
	}
	
	
	public static function remove(string $variable) :bool {
		
		// doesn't exist in the store
		if(!self::has($variable)) {
			// return false
			return(false);	
		}
		// remove it
		unlink(self::path($variable));
		// return it	
		return(!self::has($variable));
		
	}
	
}	

?>
