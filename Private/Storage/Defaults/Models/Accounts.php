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
		0	=> 'Developer',
		1	=> 'Admin',
		5	=> 'Power User',
		20	=> 'Normal User',
	];

	const VALIDATORS = [

		// using PHP's built in validators
		'login'					=>FILTER_VALIDATE_EMAIL, 
		'last_login_origin'		=>FILTER_VALIDATE_IP,
		'last_failure_origin'	=>FILTER_VALIDATE_IP,
		'creation_date'			=>FILTER_VALIDATE_INT,
		// using arrays
		'is_enabled'			=>self::IS_ENABLED,
		'id_level'				=>self::ID_LEVEL

	];

	const FILTERS = [

		// chained filters
		'login' => [
			'email',
			'strtolower',
			'length256'
		],
		
	];

	// what we mean by recent authentication failure (3 days)
	const RECENT_FAILURE = 259200;

	public function enable() :self {

		return $this->set(['is_enabled'=>'1']);

	}

	public function disable() :self {

		return $this->set(['is_enabled'=>'0']);

	}

	// retrieve all accounts
	public static function all() :array {

		return self::_select()
			->execute();

	}

	// that have been created recenlty
	public static function recentlyCreated(
		int $limit_to = 5
	) :array {
		return self::_select()
			->orderBy(['creation_date'=>'DESC'])
			->limitTo(0, $limit_to)
			->execute();
	}
	
	// that are disabled
	public static function disabled() :array {
		return self::_select()
			->where(['is_enabled'=>'0'])
			->execute();
	}

	// accounts that have had issues loging-in in recently
	public static function withErrors(
		int $recently = self::RECENT_FAILURE
	) :array {

		return self::_select()
			->whereNotEmpty(
				['last_failure_date']
			)
			->whereHigherThan(
				['last_failure_date'=> time() - $recently]
			)
			->orderBy(
				['last_failure_date'=>'DESC']
			)
			->limitTo(0, 10)
			->execute();

	}

	public function getLevel() :string {

		return self::ID_LEVEL[$this->get('id_level')];

	}

}

?>
