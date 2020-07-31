<?php

namespace Polyfony\Security;

use Polyfony\{ Locales, Entity, Exception };


class AccountsRoles extends Entity {

	// return an actual role, 
	// from its id, name, or object
	public static function getFromMixed(
		$role_id_or_name_or_object
	) :self {
		// already is an object
		if(is_object($role_id_or_name_or_object)) {
			return $role_id_or_name_or_object;
		}
		// it's its ID
		elseif(is_numeric($role_id_or_name_or_object)) {
			return new self($role_id_or_name_or_object);
		}
		// it's its name
		elseif(is_string($role_id_or_name_or_object)) {
			return new self([
				'name'=>$role_id_or_name_or_object
			]);
		}
		// wtf is this?!
		else {
			Throw Exception(
				'Cannot retrieve this Role', 
				500
			);
		}
	}

	// get accounts having been granted that role
	public function getAccounts() :array {

	}

	public function getName() :string {
		return Locales::get(
			$this->get('name', true)
		);
	}

	public function __toString() :string {

		return $this->getName();

	}

}


?>
