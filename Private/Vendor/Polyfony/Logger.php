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

			// prepare the log record with all elements necessary
			$log = array(
				'date'			=> date('d/m/y H:i:s'),
				'level'			=> self::$_levels[$level],
				'id_level'		=> $level,
				'message'		=> $message,
				'creation_date'	=> time(),
				'login'			=> Security::get('login'),
				'bundle'		=> Router::getCurrentRoute()->bundle,
				'controller'	=> Router::getCurrentRoute()->controller,
				'method'		=> Request::isPost() ? 'post' : 'get',
				'url'			=> Format::truncate(Request::getUrl(), 256),
				'ip'			=> Format::truncate(Request::server('REMOTE_ADDR'), 15),
				'agent'			=> Format::truncate(Request::server('HTTP_USER_AGENT'), 256)
			);

			// if we want to log to a file
			if(Config::get('logger', 'type') == 'file' && Config::get('logger', 'path')) {
				// format a new line for the log file
				try { self::toFile($log); } catch (Exception $e) {}
				
			}

			// if we want to log to the database
			if(Config::get('logger', 'type') == 'database') {
				// insert record in the database
				try { self::toDatabase($log); } catch (Exception $e) {}
			}

			// an email reporting address is set and level is above 2
			if(Config::get('logger', 'mail') && $level > 2) {
				// send an email notice
				try { self::toMail($log); } catch (Exception $e) { echo $e; }
			}

		}

	}

	private static function toFile($log) {
		// format the log
		unset($log['creation_date'], $log['id_level']);
		// and save it
		file_put_contents(Config::get('logger', 'path'), implode("\t", $log) . "\n", FILE_APPEND);
	}

	private static function toDatabase($log) {
		// format the log
		unset($log['date'], $log['level']);
		// and insert it
		Database::query()
		->insert($log)
		->into('Logs')
		->execute();
	}

	private static function toMail($log) {
		// format the log
		$body	 = var_export($log, true);
		$subject = 'Logger::' . self::$_levels[$log['id_level']] . "({$log['message']})";
		// and send it
		$mail = new Mail();
		$mail->to(Config::get('logger', 'mail'));
		$mail->title('Logger');
		$mail->format('text');
		$mail->subject($subject);
		$mail->body($body);
		$mail->send();
	}
	
}

?>
