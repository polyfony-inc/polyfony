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

class Database {
	
	
	// no database connection at first
	private static $_handle = null;
	// no database configuration at first
	private static $_config = null;

	// configure the database
	public static function configure($driver, $database, $hostname=null, $username=null, $password=null) {

		// alter the configuration
		self::$_config = array(
			'driver'	=> $driver,
			'database'	=> $database,
			'hostname'	=> $hostname,
			'username'	=> $username,
			'password'	=> $password,
			'quote'		=> ''
		);

	}

	// connect to the database
	public static function connect() {
	
		// if no configuration has been set, configure with the Config.ini
		self::$_config ?: self::configure(
			Config::get('database','driver'),
			Config::get('database', 'database'),
			Config::get('database', 'hostname'),
			Config::get('database', 'username'),
			Config::get('database', 'password')
		);

		// depending on the driver
		switch(self::$_config['driver']) {
			
			case 'sqlite':
				$pdo = 'sqlite:' . self::$_config['database'];
			break;
			
			case 'mysql':
				$pdo = 'mysql:dbname=' . self::$_config['database'] . 
					';host=' . self::$_config['hostname'];
				self::$_config['quote'] = '"';
			break;

			case 'postgres':
				$pdo = 'pgsql:dbname=' . self::$_config['database'] . 
					';host=' . self::$_config['hostname'];
				self::$_config['quote'] = '"';
			break;

			case 'odbc':
				$pdo = 'odbc:' . self::$_config['database'];
				self::$_config['quote'] = '"';
			break;
			
			default:
				// causes exception
				Throw new Exception('Database::connect() : Unknown driver');
			break;
			
		}

		// try to connect
		self::$_handle = new \PDO(
			$pdo, 
			self::$_config['username'] ?: null, 
			self::$_config['password'] ?: null
		);

		// check if connection the connexion failed
		if(!self::$_handle) {
			// throw an exception if it failed
			Throw new Exception('Database::connect() : Failed to connect', 500);
		}
	}
	
	// instanciate a new query object
	public static function query() {
		
		// if no connection to the database is ready
		self::$_handle ?: self::connect();
		
		// and return a new query
		return(new Query( self::$_config['quote'] ));
		
	}
	
	// give the handle to any object that requires it
	public static function handle() {
		
		// if no connection to the database is ready
		self::$_handle ?: self::connect();
		
		// return the handle
		return(self::$_handle);
		
	}
	
}	

?>
