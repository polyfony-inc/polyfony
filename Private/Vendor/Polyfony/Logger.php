<?php
/**
 * PHP Version 5
 * Simple logging class
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Logger {
	
	// list of level of log messages available
	private static $_levels = array( 0 => 'info', 1 => 'notice', 2 => 'warning', 3 => 'critical' );

	public static function info($message = null) {
		// forward to the logging method
		self::log(0, $message);
	}

	public static function notice($message = null) {
		// forward to the logging method
		self::log(1, $message);
	}

	public static function warning($message = null) {
		// forward to the logging method
		self::log(2, $message);
	}

	public static function critical($message = null) {
		// forward to the logging method
		self::log(3, $message);
	}

	private static function log($level, $message) {

		// if the logger is enabled
		if(Config::get('logger', 'enable')) {

			// if we want to log to a file
			if(Config::get('logger', 'type') == 'file' && Config::get('logger', 'path')) {
				// format a new line for the log file
				self::toFile($level, $message);
			}

			// if we want to log to the database
			if(Config::get('logger', 'type') == 'database') {
				// insert record in the database
				self::toDatabase($level, $message);
			}

			// an email reporting address is set and level is above 2
			if(Config::get('logger', 'mail') && $level > 2) {
				// send an email notice
				self::toMail($level, $message);
			}

		}

	}

	private static function toFile($level, $message) {
		// format the log
		// and save it
	}

	private static function toDatabase($level, $message) {
		// format the log
		// and insert it
	}

	private static function toMail($level, $message) {
		// format the log
		// and send it
	}
	
}

?>
