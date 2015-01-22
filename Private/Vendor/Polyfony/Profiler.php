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

	}
	
	private static function stop() {
		
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
			'memory'=> memory_get_usage()
		);
	}
	
	public static function getData() {
		// stop the profiler
		self::stop();
		// return stacked data
		return array(
			'time'	=> self::$_totalTime,
			'memory'=> self::$_totalMemory,
			'stack'	=> self::$_stack
		);
	}

	public static function getArray() {
		// return stacked data
		return array('Profiler' => self::$_stack);
	}

	public static function getHtml() {
		// prepare a container
		$stack 		= new Element('ul', array('class'=>'list-group','style'=>'float: left; margin: 15px;'));
		// prepare a title
		$marker 	= new Element('li', array('text' => 'Profiler', 'class' => 'list-group-item disabled'));
		// add the title
		$stack->adopt($marker);
		// for each element in the stack
		foreach(self::$_stack as $marker_data) {
			// prepare the name
			$marker 	= new Element('li', 	array('text' => $marker_data['name'], 'class' => 'list-group-item'));
			// prepare the time
			$time 		= new Element('span', 	array('text' => $marker_data['time'] . ' s', 'class'=>'label label-primary'));
			// prepare the memory
			$memory 	= new Element('span', 	array('text' => Format::size($marker_data['memory']), 'class'=>'label label-default'));
			// adopt the time
			$marker->setText(' ')->adopt($time)->setText(' ');
			// adopt the memory
			$marker->adopt($memory);
			// adopt the whole marker
			$stack->adopt($marker);
		}
		// return the formatted stack
		return($stack);
	}
	
}

?>
