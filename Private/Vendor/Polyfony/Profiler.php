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
	protected static $_startTime;
	protected static $_startMemory;
	protected static $_endTime;
	protected static $_endMemory;
	protected static $_totalTime;
	protected static $_totalMemory;
	
	// handles different portions of execution times
	protected static $_stack = array();

	public static function init() {
	
		// start time
		self::$_startTime = microtime(true);
		// start memory
		self::$_startMemory = memory_get_usage();

	}
	
	public static function stop() {
		
		// add end marker
		self::setMarker('end');
		// end time and end memory
		self::$_endTime		= microtime(true);
		self::$_totalTime	= self::$_endTime - self::$_startTime;
		self::$_totalMemory	= memory_get_usage();
		
	}
	
	public static function setMarker($name) {
		// profiler is disabled
		if (!Config::get('profiler', 'enable', true)) {
			return false;
		}
		// stack the marker
		self::$_stack[] = array(
			'name'	=> $name,
			'time'	=> round(microtime(true) - self::$_startTime,4),
			'memory'=> round(memory_get_usage()/1000,0)
		);
	}
	
	public static function getData() {
		// return stacked data
		return array(
			'time'	=> self::$_totalTime,
			'memory'=> self::$_totalMemory,
			'stack'	=> self::$_stack
		);
	}
	
}

?>