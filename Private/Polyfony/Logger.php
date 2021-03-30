<?php

namespace Polyfony;

use \Models\Logs as Logs;
use \Models\Emails as Emails;

class Logger {
	
	// plaintext names for each levels
	const DEBUG 	= 0;
	const INFO 		= 1;
	const NOTICE 	= 2;
	const WARNING 	= 3;
	const CRITICAL 	= 4;

	// list of level of log messages available
	const LEVELS = [
		0 => 'debug',
		1 => 'info', 
		2 => 'notice', 
		3 => 'warning',
		4 => 'critical'
	];

	// css classes
	const CLASSES = [
		0 => 'primary',
		1 => 'info', 
		2 => 'info', 
		3 => 'warning',
		4 => 'danger'
	];

	const MIN_LEVEL_FOR_EMAIL_NOTIFICATION = 3;

	public static function debug(
		$message 	= null, 
		$context 	= null
	) :void {
		// forward to the logging method
		self::log(self::DEBUG, $message, $context);
	}

	public static function info(
		$message 	= null, 
		$context 	= null
	) :void {
		// forward to the logging method
		self::log(self::INFO, $message, $context);
	}

	public static function notice(
		$message 	= null, 
		$context 	= null
	) :void {
		// forward to the logging method
		self::log(self::NOTICE, $message, $context);
	}

	public static function warning(
		$message 	= null, 
		$context 	= null
	) :void {
		// forward to the logging method
		self::log(self::WARNING, $message, $context);
	}

	public static function critical(
		$message 	= null, 
		$context 	= null
	) :void {
		// forward to the logging method
		self::log(self::CRITICAL, $message, $context);
	}

	private static function log(
		int $level, 
		$message, 
		$context = null
	) :void {
	
		// if the logger is enabled and the log event level is supposed to be logged
		if(
			Config::get('logger', 'enable') && 
			$level >= Config::get('logger', 'level')
		) {

			// format the log event
			$log = self::formatLogMessage($level, $message, $context);
			
			// if the profiler is enabled
			if(Config::get('profiler', 'enable')) {
				// send the log event to the profiler
				try { self::toProfiler($log); } catch (Exception $e) {}
			}
			
			// if we want to log to a file
			if(
				Config::get('logger', 'driver') == 'file' && 
				Config::get('logger', 'path')
			) {
				// format a new line for the log file
				try { self::toFile($log); } catch (Exception $e) {}
			}

			// if we want to log to the database
			if(Config::get('logger', 'driver') == 'database') {
				// insert record in the database
				try { self::toDatabase($log); } catch (Exception $e) {}
			}
			
			// an email reporting address is set and level is above 3
			if(
				Config::get('logger', 'email') && 
				$level > self::MIN_LEVEL_FOR_EMAIL_NOTIFICATION
			) {
				// send an email notice
				try { self::toEmail($log); } catch (Exception $e) {}
			}

		}

	}

	// format a log message with proper context and additionnal informations
	private static function formatLogMessage(
		int $level, 
		$message, 
		$context = null
	) :array {

		return [
			'date'			=> date('d/m/y H:i:s'),
			'level'			=> self::LEVELS[$level],
			'id_level'		=> $level,
			'message'		=> $message,
			'context'		=> $context,
			'creation_date'	=> time(),
			'login'			=> Security::isAuthenticated() ? Security::getAccount()->get('login') : '',
			'bundle'		=> isset(Router::getCurrentRoute()->bundle) ? Router::getCurrentRoute()->bundle : null,
			'controller'	=> isset(Router::getCurrentRoute()->controller) ? Router::getCurrentRoute()->controller : null ,
			'method'		=> Request::isPost() ? 'post' : 'get',
			'url'			=> Format::truncate(Request::getUrl(), 256),
			'ip'			=> Format::truncate(Request::server('REMOTE_ADDR'), 15),
			'agent'			=> Format::truncate(Request::server('HTTP_USER_AGENT'), 256)
		];

	}

	private static function toProfiler( array $log ) :void {

		// place a marker and auto release
		Profiler::releaseMarker(
				Profiler::setMarker(
				$log['message'],
				'log',
				$log
			)
		);

	}

	private static function toFile( array $log ) :void {
		// add the context to the message
		$log['message'] .= ' '.json_encode($log['context']);
		// format the log
		unset($log['creation_date'], $log['id_level'], $log['context']);
		// add the debug informations
		// and save it
		file_put_contents(Config::get('logger', 'path'), implode("\t", $log) . "\n", FILE_APPEND);
	}

	private static function toDatabase( array $log ) :void {
		// add the context to the message
		$log['message'] .= ' '.json_encode($log['context']);
		// remove useless element
		unset($log['date'], $log['level'], $log['context']);
		// and insert it
		Logs::create($log);
	}

	private static function toEmail( array $log ) :void {
		// format and send and email with the log element
		(new Emails)
			->set([
				'to' 		=> Config::get('logger', 'email'),
				'format' 	=> 'text',
				'subject' 	=> ucfirst(self::LEVELS[$log['id_level']]) . " : {$log['message']}",
				'body' 		=> var_export($log, true),
			])
			->send();
	}
	
}

?>
