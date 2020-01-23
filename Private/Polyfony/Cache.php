<?php

namespace Polyfony;

class Cache {

	// number of cache hits
	private static $hits_count = 0;
	// number of cache misses
	private static $misses_count = 0;
	// elapsed time putting into the cache
	private static $cache_in_time = 0;
	// elapsed time retrieving from cache
	private static $cache_out_time = 0;

	public static function getStatistics() :array {
		return [
			'hits_count'	=>self::$hits_count,
			'misses_count'	=>self::$misses_count,
			'cache_in_time'	=>self::$cache_in_time,
			'cache_out_time'=>self::$cache_out_time,
		];
	}

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
			// we don't have that element
			return false;
		}

	}
	

	public static function put(string $variable, $value=null, $overwrite=false, $lifetime=false) :bool {
	
		// already exists and no overwrite
		if(self::has($variable) && !$overwrite) {
			// throw an exception
			Throw new \Polyfony\Exception("{$variable} already exists in the store.");
		}
		// only if the profiler is enabled
		if(Config::get('profiler','enable')) {
			// feed the statistics
			self::$misses_count++;
			$start = microtime(true);
		}
		// store it
		file_put_contents(self::path($variable), msgpack_pack($value));
		// compute the expiration time or set to a year by default
		$lifetime = $lifetime ? time() + $lifetime : time() + 365 * 24 * 3600;
		// alter the modification time
		touch(self::path($variable), $lifetime);
		// only if the profiler is enabled
		if(Config::get('profiler','enable')) {
			self::$cache_in_time += microtime(true) - $start;
		}
		// return status
		return(self::has($variable));
		
	}
	

	public static function get(string $variable) {

		// doesn't exist in the store
		if(!self::has($variable)) {
			// throw an exception
			Throw new \Polyfony\Exception("{$variable} does not exist in the store.");
		}
		// only if the profiler is enabled
		if(Config::get('profiler','enable')) {
			// feed the statistics
			self::$hits_count++;
			$start = microtime(true);
		}
		// get it, unpack it 
		$cache_item = msgpack_unpack(file_get_contents(self::path($variable)));
		// only if the profiler is enabled
		if(Config::get('profiler','enable')) {
			self::$cache_out_time += microtime(true) - $start;
		}
		return $cache_item;
		
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
