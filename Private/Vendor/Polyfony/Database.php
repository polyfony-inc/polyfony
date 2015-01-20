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
	protected static $_handle = null;
	
	// connect to the database
	public static function connect() {
	
		// depending on the driver
		switch(Config::get('database','driver')) {
			
			case 'sqlite':
				$pdo = 'sqlite:' . Config::get('database', 'database');
			break;
			
			case 'mysql':
				$pdo = 'mysql:dbname=' . Config::get('database', 'database') . 
				';host=' . Config::get('database', 'hostname');
			break;
			
			default:
				// causes exception
				Throw new Exception('Database::connect() : Unknown driver');
			break;
			
		}

		// try to connect
		self::$_handle = new \PDO(
			$pdo, 
			Config::get('database', 'username') ?: null, 
			Config::get('database', 'password') ?: null
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
		return(new Query());
		
	}
	
	// give the handle to any object that requires it
	public static function handle() {
		
		// return the handle
		return(self::$_handle);
		
	}
	
}	

?>
