<?php

namespace Polyfony;
 
class Events {

	private static array $tasks = [
		'onTerminate'				=>[],
//		'beforeRender'				=>[], // might be implemented later on
//		'afterRender'				=>[], // might be implemented later on
//		'beforeQuery'				=>[], // might be implemented later on
//		'afterQuery'				=>[], // might be implemented later on
//		'beforeRouting'				=>[], // might be implemented later on
//		'afterRouting'				=>[], // might be implemented later on
//		'exceptionThrown'			=>[], // might be implemented later on
//		'successfullLogin'			=>[], // might be implemented later on
//		'failedLogin'				=>[], // might be implemented later on
//		'successfulAuthentication'	=>[], // might be implemented later on
	];

	// register a task to a specific event
	public static function register(
		string $event_to_watch_for,
		\Closure $task, 
		array $arguments = []
	) :void {

		// push the event in the pool
		self::$tasks[$event_to_watch_for][] = [
			'closure'	=> $task, 
			'arguments'	=> $arguments
		];

	}

	// trigger all task related to a specific event
	public static function trigger(
		string $event_name
	) :void {

		if(
			isset(self::$tasks[$event_name]) && 
			is_array(self::$tasks[$event_name])
		) {
			// for each tasks triggered by this event
			foreach(self::$tasks[$event_name] as $tasks) {
				// run the tasks
				call_user_func_array(
					$tasks['closure'],
					$tasks['arguments']
				);
			}
		}
	
	}

}

?>
