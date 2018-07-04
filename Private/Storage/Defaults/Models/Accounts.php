<?php

namespace Models;
use Polyfony as pf;

// this is a model class
// it's a repository for stored SQL queries
// there should not be any functionnal code or conditions here

class Accounts extends \Polyfony\Security\Accounts {
	
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

		// using PHP's built in validators
		'login'					=>FILTER_VALIDATE_EMAIL, 
		'last_login_origin'		=>FILTER_VALIDATE_IP,
		'last_failure_origin'	=>FILTER_VALIDATE_IP,

		// using arrays
		'is_enabled'=>self::IS_ENABLED,
		'id_level'	=>self::ID_LEVEL

	];

	const FILTERS = [

		// chained filters
		'login'=>['email','length256'],
		
	];

	// what we mean by recent authentication failure (3 days)
	const RECENT_FAILURE = 259200;

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
