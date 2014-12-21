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
	
	
	public static function connect() {
	
		// depending on the driver
		switch(Config::get('database','driver')) {
			
			case 'sqlite':
				$pdo = 'sqlite:' . Config::get('database', 'database');
			break;
			
			case 'mysql':
				$pdo = 'mysql:dbname=' . Config::get('database', 'database') . ';host=' . Config::get('database', 'hostname');
			break;
			
			default:
				// causes exception
				Throw new Exception('Database::connect() : Unknown driver');
			break;
			
		}

		// try to connect
		self::$_handle = new \PDO($pdo, Config::get('database', 'username'), Config::get('database', 'password'));
		/*
		// check if connection the connexion failed
		!self::$_handle ? Throw new \Exception('Database::connect() : Failed to connect');
		*/
	}
	
	public static function query() {
		
		// if no connection to the database is ready
		!self::$_handle ? self::connect();
		
		// and return a new query
		return(new Query());
		
	}
	
}	

?>