<?php

namespace Polyfony;

/** 
 * The throttle allows to rate-limit endpoints or features.
 * It uses the route name and IP by default, but your can define your own key
 */ 
class Throttle {

	public static function enforce(
		int $limit_to, 
		int $timeframe, 
		string $key
	) :void {

		// check if we are being rate limited
		if(apcu_exists(self::getPrefixedLockedKey($key))) {
			// stop here if we have a lock
			Throw new \Polyfony\Exception('Throttle::enforce() You are being rate-limited', 429);
		}

		// check if we have already something stored
		if($hits = apcu_fetch(self::getPrefixedHitsKey($key))) {
			// add a new hit in the list of hits
			array_push(
				$hits, 
				microtime(true)
			);
		}
		// we have not hits registered yet
		else {
			// so we declare an array with our first hit
			$hits = [microtime(true)];
		}
		// save the hit and create a lock if necessary
		self::saveHit($limit_to, $timeframe, $key, $hits);

	}

	private static function getPrefixedHitsKey(string $key) :string {
		return Config::get('throttle','prefix') . $key . '::HITS';
	}

	private static function getPrefixedLockedKey(string $key) :string {
		return Config::get('throttle','prefix') . $key . '::LOCK';
	}

	public static function perSecond(
		int $limit_to, 
		string $key
	) {
		self::enforce($limit_to, 1, $key);
	}

	public static function perMinute(
		int $limit_to, 
		string $key
	) {
		self::enforce($limit_to, 60, $key);
	}

	public static function perHour(
		int $limit_to, 
		string $key
	) {
		self::enforce($limit_to, 3600, $key);
	}

	public static function perDay(
		int $limit_to, 
		string $key
	) {
		self::enforce($limit_to, 86400, $key);
	}


	private static function saveHit(
		int $limit_to, 
		int $timeframe, 
		string $key,
		array $hits
	) {
		
		// if we have more hit than allowed
		$hits = count($hits) > $limit_to ?
			// only get the last ones (using the "limit_to", EX. if we limit to 5 hits, we only keep the last 5 hits) 
			array_slice($hits, -$limit_to) : 
			// if we don't have enough entry yet, don't bother keep the whole list of hits
			$hits;
		
		if(
			// if we have reached the maximum number of hits
			count($hits) == $limit_to && 
			// and if the oldest one is still in the timeframe
			(int) $hits[0] > time() - $timeframe
		) {
			// the lock's ttl is the current time, minus oldest hit of the list + timeframe
			// this provides a leaky bucket with a lock expiring at the oldest hit date + timeframe
			$ttl =  $timeframe - (time() - (int) $hits[0]);
			// create the lock (avoiding 0 ttl, never expiring ones)
			apcu_store(self::getPrefixedLockedKey($key), true, ($ttl == 0 ? 1 : $ttl) );
		}

		// store the last "limit to" hits 
		apcu_store(
			self::getPrefixedHitsKey($key), 
			$hits,
			// define the ttl (auto-expunge older than one day if nothing is specified)
			$timeframe
		);
		
	}

	

}

?>
