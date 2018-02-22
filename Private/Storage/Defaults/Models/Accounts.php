<?php

namespace Models;
use Polyfony as pf;

// this is a model class
// it's a repository for stored SQL queries
// there should not be any functionnal code or conditions here

class Accounts extends pf\Record {
	
	const IS_ENABLED = [
		0=>'No',
		1=>'Yes'
	];

	const ID_LEVEL = [

		0	=> 'God',
		1	=> 'Admin',
		5	=> 'SuperUser',
		20	=> 'User',

	];

	const VALIDATORS = [

//		'login'		=>'/^\S+@\S+\.\S+$/', // commented out because the demo uses simply "root" as login
		'IS_ENABLED'=>self::IS_ENABLED,
		'ID_LEVEL'	=>self::ID_LEVEL

	];

	// what we mean by recent authentication failure (3 days)
	const RECENT_FAILURE = 259200;

	public function hasModule($searched_module) :bool {

		return in_array(
			$searched_module, 
			$this->get('modules_arrray')
		);

	}

	public function enable() :self {

		return $this->set('IS_ENABLED', '1');

	}

	public function disable() :self {

		return $this->set('IS_ENABLED', '0');

	}

	// retrieve all accounts
	public static function all() :array {

		return pf\Database::query()
			->select()
			->from('Accounts')
			->execute();

	}

	// that have been created recenlty
	public static function recentlyCreated($maximum=5) :array {
		return(\Polyfony\Database::query()
			->select()
			->from('Accounts')
			->orderBy(array('creation_date'=>'DESC'))
			->limitTo(0,$maximum)
			->execute()
		);
	}
	
	// that are disabled
	public static function disabled() :array {
		return(\Polyfony\Database::query()
			->select()
			->from('Accounts')
			->where(array('IS_ENABLED'=>'0'))
			->execute()
		);
	}

	// accounts that have had issues login in recently
	public static function withErrors() :array {

		return pf\Database::query()
			->select()
			->from('Accounts')
			->whereNotEmpty('last_failure_date')
			->whereHigherThan('last_failure_date', time() - self::RECENT_FAILURE )
			->limitTo(0, 10)
			->orderBy(array('last_failure_date'=>'DESC'))
			->execute();

	}

}

?>
