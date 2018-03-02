<?php

namespace Polyfony;

class Database {
	
	
	// no database connection at first
	private static $_handle = null;
	// no database configuration at first
	private static $_config = null;

	// configure the database
	public static function configureAndGetDSN() :string {

		// alter the configuration
		self::$_config = array(
			'driver'	=> Config::get('database','driver'),
			'database'	=> Config::get('database', 'database'),
			'hostname'	=> Config::get('database', 'hostname'),
			'username'	=> Config::get('database', 'username'),
			'password'	=> Config::get('database', 'password'),
			'before'	=> Config::get('database', 'before'),
			'quote'		=> '',
			'nulls'		=> [
				'query'		=>'',
				'column'	=>'',
				'true'		=>'',
				'false'		=>'',
			]
		);

		// depending on the driver
		switch(self::$_config['driver']) {
			
			case 'sqlite':
				$dsn = self::configureForSQLite();
			break;
			
			case 'mysql':
				$dsn = self::configureForMySQL();
			break;

			case 'postgres':
				$dsn = self::configureForPGSql();
			break;

			case 'odbc':
				$dsn = self::configureForODBC();
			break;
			
			default:
				// causes exception
				Throw new Exception('Database::connect() : Unknown driver');
			break;
			
		}

		// the dsn we have assembled
		return $dsn;

	}

	// connect to the database
	public static function connect() :void {

		// configure the database and get the dns
		$dsn = self::configureAndGetDSN();

		// try to connect
		self::$_handle = new \PDO(
			$dsn, 
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
	
	private static function configureForSQLite() :string {
		
		// configure
		self::$_config['nulls'] = [
			'query'		=>'PRAGMA table_info( *table* )',
			'column'	=>'notnull',
			'true'		=>'0',
			'false'		=>'1',
		];

		// return the dsn
		return 'sqlite:' . self::$_config['database'];
	}

	private static function configureForMySQL() :string {
		
		// configure 
		self::$_config['quote'] = '"';
		self::$_config['nulls'] = [
			'query'		=>'DESCRIBE "*table*"',
			'column'	=>'Null',
			'true'		=>'YES',
			'false'		=>'NO',
		];

		// return the dsn
		return 'mysql:dbname=' . self::$_config['database'] . ';host=' . self::$_config['hostname'];
	}

	private static function configureForPGSql() :string {
		
		// configure
		self::$_config['quote'] = '"';

		// return the dns
		return 'pgsql:dbname=' . self::$_config['database'] . ';host=' . self::$_config['hostname'];
	}

	private static function configureForODBC() :string {

		// configure 
		self::$_config['quote'] = '"';

		// return the dsn
		return 'odbc:' . self::$_config['database'];
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
