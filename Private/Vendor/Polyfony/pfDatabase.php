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

class pfDatabase {
	
	
	// no database connection at first
	protected static $_handle = null;
	
	
	public static function connect() {
	
		// if driver is sqlite
		pfConfig::get('database','driver') == 'sqlite' ? $pdo = 'sqlite:'.pfConfig::get('database','database');
		
		// if driver is mysql
		pfConfig::get('database','driver') == 'mysql' ? $pdo = 'mysql:dbname='.pfConfig::get('database','database').';host='pfConfig::get('database','hostname');
		
		// if driver is unknown
		!$pdo ? Throw new pfException('pfDatabase::connect() : Unknown driver');
		
		// try to connect
		self::$_handle = new PDO($pdo,pfConfig::get('database','username'),pfConfig::get('database','password'));
		
		// check if connection the connexion failed
		!self::$_handle ? Throw new pfException('pfDatabase::connect() : Failed to connect');
		
	}
	
	public static function query() {
		
		// if no connection to the database is ready
		!self::$_handle ? self::connect();
		
	}
	
}	

?>