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

	const VALIDATORS = [

		// using PHP's built in validators
		'login'					=>FILTER_VALIDATE_EMAIL, 
		'creation_date'			=>FILTER_VALIDATE_INT,
		// using arrays
		'is_enabled'			=>self::IS_ENABLED

	];

	const FILTERS = [

		// chained filters
		'login' => [
			'email',
			'strtolower',
			'length256'
		],
		'firstname' => [
			'text',
			'trim',
			'strtolower',
			'ucwords',
			'length32'
		],
		'lastname' => [
			'text',
			'trim',
			'strtoupper',
			'length32'
		]
		
	];

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

	public function getRolesBadges() :string {

		$roles = [];

		foreach(
			$this->getRoles() as 
			$role
		) {
			$roles[] = $role->getBadge();
		}

		return implode(' ', $roles);

	}

	public function getPermissionsBadges() :string {

		$permissions = [];

		foreach(
			$this->getPermissions(true) as 
			$permission
		) {
			$permissions[] = $permission->getBadge();
		}

		return implode(' ', $permissions);

	}


}

?>
