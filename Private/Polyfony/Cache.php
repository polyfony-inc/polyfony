<?php

namespace Polyfony;

class Cache {

	// number of cache hits (when data is successfully retrieved from the cache)
	private static int $hits_count = 0;
	// number of cache misses (when data is not found in the cache)
	private static int $misses_count = 0;
	// total time spent putting items into the cache
	private static float $cache_in_time = 0;
	// total time spent retrieving items from the cache
	private static float $cache_out_time = 0;

	// Return the cache statistics (hits, misses, cache in time, cache out time)
	public static function getStatistics() :array {
		return [
			'hits_count'	=>self::$hits_count, // total number of cache hits
			'misses_count'	=>self::$misses_count, // total number of cache misses
			'cache_in_time'	=>self::$cache_in_time, // total time spent caching items
			'cache_out_time'=>self::$cache_out_time, // total time spent retrieving items from cache
		];
	}

	// Prefix a variable key for caching
	private static function getPrefixedKey($variable) :string {

		// Sanitize the variable name to make it filesystem safe
		$variable = \Polyfony\Format::fsSafe($variable);
		// Add a prefix to the variable to create a unique cache key
		return Config::get('apcu', 'prefix') . '::CACHE::' .$variable;

	}

	// Check if a variable exists in the cache
	public static function has(string $variable) :bool {

		// Get the prefixed key for the variable
		$getPrefixedKey = self::getPrefixedKey($variable);
		// Return true if the item exists in the cache, false otherwise
		return apcu_exists($getPrefixedKey);

	}
	
	// Put an item into the cache
	public static function put(
		string $variable,  // The cache key
		$value = null,     // The value to be stored in the cache
		$overwrite = false, // Whether to overwrite existing value
		$lifetime = false   // Optional lifetime of the cache item (in seconds)
	) :bool {
	
		// If the variable exists in the cache and overwrite is not allowed, throw an exception
		if (self::has($variable) && !$overwrite) {
            throw new \Polyfony\Exception("{$variable} already exists in the store.");
        }
        // If profiling is enabled, increment the cache misses and start the timer
        if (Config::get('profiler', 'enable')) {
            self::$misses_count++;
            $start = microtime(true);
        }
        // Get the cache key with the appropriate prefix
        $key = self::getPrefixedKey($variable);
        // Set the time-to-live (TTL) for the cache item (default to one year if not provided)
        $ttl = $lifetime ?? 365 * 24 * 3600; // Default to one year
        // Store the value in the cache with the calculated TTL
        apcu_store($key, $value, $ttl);
        // If profiling is enabled, calculate and store the time spent adding the item to the cache
        if (Config::get('profiler', 'enable')) {
            self::$cache_in_time += microtime(true) - $start;
        }
        // Return true if the item now exists in the cache, false otherwise
        return self::has($variable);
		
	}
	
	// Get an item from the cache
	public static function get(string $variable) {

		// If the variable does not exist in the cache, throw an exception
		if (!self::has($variable)) {
            throw new \Polyfony\Exception("{$variable} does not exist in the store.");
        }
        // If profiling is enabled, increment the cache hits and start the timer
        if (Config::get('profiler', 'enable')) {
            self::$hits_count++;
            $start = microtime(true);
        }
        // Get the cache key with the appropriate prefix
        $key = self::getPrefixedKey($variable);
        // Fetch the item from the cache, store the success flag
        $cache_item = apcu_fetch($key, $success);
        // If fetching the item fails, throw an exception
        if (!$success) {
            throw new \Polyfony\Exception("Failed to retrieve {$variable} from the store.");
        }
        // If profiling is enabled, calculate and store the time spent retrieving the item from the cache
        if (Config::get('profiler', 'enable')) {
            self::$cache_out_time += microtime(true) - $start;
        }
        // Return the cached item
        return $cache_item;
		
	}
	
	// Remove an item from the cache
	public static function remove(string $variable) :bool {
		
		// If the variable does not exist in the cache, return false
		if (!self::has($variable)) {
            return false;
        }
        // Get the cache key with the appropriate prefix
        $key = self::getPrefixedKey($variable);
        // Delete the item from the cache
        apcu_delete($key);
        // Return true if the item was successfully deleted, false otherwise
        return !self::has($variable);
		
	}
	
}
