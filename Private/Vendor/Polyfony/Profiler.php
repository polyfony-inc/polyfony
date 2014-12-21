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

class Profiler {

	// handles gobal execution time
	protected static $_requestStart;
	protected static $_requestEnd;
	protected static $_requestDuration;
	
	// handles different portions of execution times
	protected static $_stack = array();

	public static function init() {
	
		self::$_requestStart = microtime(true);
		
	}
	
	public static function stop() {
		
		self::$_requestEnd = microtime(true);
		self::$_requestDuration = self::$_requestEnd - self::$_requestStart;
			
	}
	
	public static function register($type,$name) {
		// Profile this request?
		if (! Config::get('profiler', 'enable', true)) {
			return false;
		}
		array_unshift(self::$_stack, array(
			'type'  => $type,
			'name'  => $name,
			'start' => microtime(true),
			'mem'   => memory_get_usage()
		));
	}
	
	public static function unregister($type,$name) {
		// Profile this request?
		if (! Config::get('profiler', 'enable', true)) {
			return false;
		}
		// Get the time here to get a more accurate trace
		$microtime = microtime(true);
		// And begin the loop
		foreach (self::$_stack as $traceId => $trace) {
			if ($trace['name'] == $name) {
				self::$_stack[$traceId]['end'] = $microtime;
				self::$_stack[$traceId]['mem'] =
					memory_get_usage() - self::$_stack[$traceId]['mem'];
				break;
			}
		}
	}
	
	public static function getProfilerData() {
		return array(
			'requestStart' => self::$_requestStart,
			'requestEnd'   => self::$_requestEnd,
			'stack'        => array_reverse(self::$_stack)
		);
	}
	
}

?>