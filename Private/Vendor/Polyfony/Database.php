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
	public static function configure(
		string $driver, 
		string $database, 
		string $hostname=null, 
		string $username=null, 
		string $password=null, 
		string $before=null
	) :void {

		// alter the configuration
		self::$_config = array(
			'driver'	=> $driver,
			'database'	=> $database,
			'hostname'	=> $hostname,
			'username'	=> $username,
			'password'	=> $password,
			'before'	=> $before,
			'quote'		=> '',
			'nulls'		=> [
				'query'		=>'',
				'column'	=>'',
				'true'		=>'',
				'false'		=>'',
			]
		);

	}

	// connect to the database
	public static function connect() :void {
	
		// if no configuration has been set, configure with the Config.ini
		self::$_config ?: self::configure(
			Config::get('database','driver'),
			Config::get('database', 'database'),
			Config::get('database', 'hostname'),
			Config::get('database', 'username'),
			Config::get('database', 'password'),
			Config::get('database', 'before')
		);

		// depending on the driver
		switch(self::$_config['driver']) {
			
			case 'sqlite':
				$pdo = 'sqlite:' . self::$_config['database'];
				self::$_config['nulls'] = [
					'query'		=>'PRAGMA table_info( *table* )',
					'column'	=>'notnull',
					'true'		=>'0',
					'false'		=>'1',
				];
			break;
			
			case 'mysql':
				$pdo = 'mysql:dbname=' . self::$_config['database'] . 
					';host=' . self::$_config['hostname'];
				self::$_config['quote'] = '"';
				self::$_config['nulls'] = [
					'query'		=>'DESCRIBE "*table*"',
					'column'	=>'Null',
					'true'		=>'YES',
					'false'		=>'NO',
				];
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

		// if a before statement has to be executed
		if(self::$_config['before']) {
			// execute the before statement
			self::$_handle->query(self::$_config['before']);
		}
	}
	
	// instanciate a new query object
	public static function query() :Query {
		
		// if no connection to the database is ready
		self::$_handle ?: self::connect();
		
		// and return a new query
		return(new Query( self::$_config['quote'] ));
		
	}
	
	// give the handle to any object that requires it
	public static function handle() :\PDO{
		
		// if no connection to the database is ready
		self::$_handle ?: self::connect();

		// return the handle
		return(self::$_handle);
		
	}

	// get the description of a table
	public static function describe(string $table) :array {

		// set the cachefile name
		$cache_name = ucfirst($table).'Nulls';

		// check if it has been cached already
		if(Cache::has($cache_name)) {

			// get it from the cache
			return Cache::get($cache_name);

		}
		// else it is not available from the cache
		else {

			// the list of allowed null columns
			$allowed_nulls = [];

			// query the database
			foreach(self::query()
				->query(str_replace('*table*', $table, self::$_config['nulls']['query']))
				->execute() as $column) {

				// populate the list
				$allowed_nulls[$column->get('name')] = 
					self::$_config['nulls']['true'] == $column->get(self::$_config['nulls']['column']) ?
						true : false;

			}

			// save the results in the cache
			Cache::put($cache_name, $allowed_nulls);

			// and finaly return the results
			return $allowed_nulls;

		}

	}
	
}	

?>
