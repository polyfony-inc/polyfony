<?php

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

	// users of ressources
	const USERS = [
		'framework', 'controller', 'view', 'database', 'email', 'user'
	];

	// important parameters to look for
	const IMPORTANT_PHP_INI = [
		'memory_limit',
		'max_execution_time',
		'post_max_size',
		'upload_max_filesize',
		'max_file_uploads'
	];

	public static function init() :void {

		// start time
		self::$_startTime = microtime(true);

	}
	
	private static function stop() :void {

		// for each unreleased marker
		foreach(self::$_stack as $name => $element) {
			if($element['duration'] === null) {
				// manually release it
				self::releaseMarker($element['name']);
			}
		}
//		var_dump(self::$_stack);die();
		// end time and end memory
		self::$_endTime		= microtime(true);
		self::$_totalTime	= self::$_endTime - self::$_startTime;
		self::$_totalMemory	= memory_get_usage();
		
	}
	
	public static function setMarker($name=null, $user=null, $additional_infos=[], $force=false) {
		// profiler is disabled (forcing allows markers to be set before the configuration is made available)
		if (!Config::get('profiler', 'enable') && !$force) {
			return false;
		}
		// check the user name
		$user = in_array($user, self::USERS) ? $user : 'user';
		// generate a marker name
		$name = $name ? $name : ucfirst($user).'-'.count(self::$_stack);
		// stack the marker
		self::$_stack[$name] = array(
			'name'			=> $name,
			'user'			=> $user,
			'start'			=> microtime(true),
			'duration'		=> null,
			'memory'		=> memory_get_usage(),
			'informations'	=> $additional_infos
		);
		// return the name, for releasing it later
		return $name;
	}

	public static function releaseMarker($name, $force=false) :void {

		if(
			(
				Config::get('profiler', 'enable') && 
				array_key_exists($name, self::$_stack)
			) || $force === true
		) {
			self::$_stack[$name]['duration'] 		= microtime(true) - self::$_stack[$name]['start'];
			self::$_stack[$name]['memory'] 			= memory_get_usage() - self::$_stack[$name]['memory'];
		}
		

	}
	
	public static function getData() :array {
		// stop the profiler
		self::stop();
		// return stacked data
		return array(
			'time'			=> self::$_totalTime,
			'start_time'	=> self::$_startTime,
			'memory'		=> self::$_totalMemory,
			'stack'			=> self::$_stack
		);
	}

	public static function getFootprint() :string {

		// get the data
		$data = self::getData();
		// assemble and return memory with time
		return round($data['time'] * 1000, 1) . ' ms '. Format::size($data['memory']);

	}

	public static function getArray() :array {
		// return stacked data
		return array('Profiler' => self::getData());
	}
	
}

?>
