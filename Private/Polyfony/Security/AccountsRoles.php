<?php

namespace Polyfony\Security;

use Polyfony\{ Locales, Entity, Exception };


class AccountsRoles extends Entity {

	// return an actual role, 
	// from its id, name, or object
	public static function getFromMixed(
		string|int|AccountsRoles $role_id_or_name_or_object
	) :self {
		// already is an object
		if(is_object($role_id_or_name_or_object)) {
			return $role_id_or_name_or_object;
		}
		// it's its ID
		elseif(is_numeric($role_id_or_name_or_object)) {
			return new \Models\AccountsRoles($role_id_or_name_or_object);
		}
		// it's its name
		elseif(is_string($role_id_or_name_or_object)) {
			return new \Models\AccountsRoles([
				'name'=>$role_id_or_name_or_object
			]);
		}
		// wtf is this?!
		else {
			Throw new Exception(
				'Cannot retrieve this Role', 
				500
			);
		}
	}

	// get accounts having been granted that role
	public function getAccounts() :array {

		return \Models\Accounts::_select([
				'Accounts.*'
			])
			->join(
				'AccountsRolesAssigned', 
				'AccountsRolesAssigned.id_account',
				'Accounts.id',
			)
			->where(['AccountsRolesAssigned.id_role'=>$this->get('id')])
			->execute();

	}

	// get permissions that are inherited from this role
	public function getPermissions(
		// if we want to keep a trace of what that permission whas inherited from
		bool $with_parent_role_id = false
	) :array {

		return \Models\AccountsPermissions::_select(
				($with_parent_role_id ? 
					[
						'AccountsPermissions.*', 
						'AccountsRolesAssigned.id_role'
					] : 
					['AccountsPermissions.*'])
			)
			->join(
				'AccountsPermissionsAssigned', 
				'AccountsPermissions.id', 
				'AccountsPermissionsAssigned.id_permission'
			)
			->where(['AccountsPermissionsAssigned.id_role'=>$this->get('id')])
			->execute();

	}

	public function addPermission(
		AccountsPermissions $permission
	) :bool {

		return (new \Models\AccountsPermissionsAssigned)
			->set([
				'id_role'		=>$this->get('id'),
				'id_permission'	=>$permission->get('id')
			])->save();

	}

	public function removePermission(
		AccountsPermissions $permission
	) :bool {

		return (bool) (new \Models\AccountsPermissionsAssigned([
				'id_role'		=>$this->get('id'),
				'id_permission'	=>$permission->get('id')
			]))
			->delete();

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
