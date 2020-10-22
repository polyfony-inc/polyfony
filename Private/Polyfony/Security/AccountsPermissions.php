<?php

namespace Polyfony\Security;

use Polyfony\{ Locales, Entity };


class AccountsPermissions extends Entity {

	// return an actual permission, 
	public static function getFromMixed(
		$permission_id_or_name_or_object
		// string|int|AccountsPermissions $permission // PHP 8 only
	) :\Models\AccountsPermissions {
		
		// if we were provided with its name
		if(is_string($permission_id_or_name_or_object)) {
			return new \Models\AccountsPermissions(['name'=>$permission_id_or_name_or_object]);
		}
		// if we were provided with its ID
		elseif(is_int($permission_id_or_name_or_object)) {
			return new \Models\AccountsPermissions($permission_id_or_name_or_object);
		}
		// if we were provided with the actual permission object
		elseif(is_object($permission_id_or_name_or_object)) {
			return $permission_id_or_name_or_object;
		}
		// wtf is this?!
		else {
			Throw new Exception(
				'Cannot retrieve this Permission', 
				500
			);
		}
		
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

		// select from RolesPermissionsAssigned
		return \Models\AccountsRoles::_select([
				'AccountsRoles.*'
			])
			->join(
				'AccountsPermissionsAssigned', 
				'AccountsPermissionsAssigned.id_role',
				'AccountsRoles.id'
			)
			->where(['AccountsPermissionsAssigned.id_permission'=>$this->get('id')])
			->execute();

	}

	// get accounts having been granted that permission
	public function getAccounts() :array {

		
		return array_unique(array_merge(
			// get account with that permissions directly assigned to them
			\Models\Accounts::_select([
					'Accounts.*'
				])
				->join(
					'AccountsPermissionsAssigned', 
					'AccountsPermissionsAssigned.id_account',
					'Accounts.id',
				)
				->where(['AccountsPermissionsAssigned.id_permission'=>$this->get('id')])
				->execute(), 
			// get account that inherit that permission from a role assigned to them
			\Models\Accounts::_select([
					'Accounts.*'
				])
				->join(
					'AccountsRolesAssigned', 
					'AccountsRolesAssigned.id_account',
					'Accounts.id',
				)
				->join(
					'AccountsRoles',
					'AccountsRoles.id',
					'AccountsRolesAssigned.id_role'
				)
				->join(
					'AccountsPermissionsAssigned', 
					'AccountsPermissionsAssigned.id_role',
					'AccountsRoles.id',
				)
				->where(['AccountsPermissionsAssigned.id_permission'=>$this->get('id')])
				->execute()
		));

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
