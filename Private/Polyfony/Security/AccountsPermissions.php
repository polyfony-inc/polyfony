<?php

namespace Polyfony\Security;

use Polyfony\{ Locales, Entity };


class AccountsPermissions extends Entity {

	public static function getFromMixed() :self {
		// return an actual permission, 
		// from its id, name, or object
	}

	// get the role from which that permissions has been inherited, if any
	// only works on joined permissions selections, such as provided by the Accounts entity
	public function getRole() :?AccountsRoles {

		return $this->get('id_role') ? 
			new \Models\AccountsRoles($this->get('id_role')) : 
			null;
	}

	// get roles granting this permission
	public function getRoles() :array {

	}

	// get accounts having been granted that permission
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
