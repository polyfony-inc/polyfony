<?php 

namespace Polyfony\Security\Accounts;

// framework internals
use Polyfony\Security as Security;
use Polyfony\Config as Config;
use Polyfony\Logger as Logger;
use Polyfony\Store\Cookie as Cookie;

// models
use \Models\{
	Accounts as ExtendedAccounts,
	AccountsSessions,
	AccountsLogins,
	AccountsPermissions,
	AccountsPermissionsAssigned,
	AccountsRolesAssigned,
	AccountsRoles
};

class PermissionsAccessors extends \Polyfony\Entity {

	public function getRoles() :array {

		// maybe		
		// cache roles in ->_['roles']			

		$roles = AccountsRoles::_select(['AccountsRoles.*'])
			->join(
				'AccountsRolesAssigned',
				'AccountsRolesAssigned.id_role',
				'AccountsRoles.id'
			)
			->where(['AccountsRolesAssigned.id_account'=>$this->get('id')])
			->execute();

		return $roles;
	}

	public function hasRole(
		string|int|AccountsRoles $mixed_role
	) :bool {
		$role = AccountsRoles::getFromMixed(
			$mixed_role
		);
		
		return in_array(
			$role, 
			$this->getRoles()
		);
	}

	public function getPermissions(
		// if we want to keep a trace of what that permission whas inherited from
		bool $with_parent_role_id = false
	) :array {

		$cache_variable_name = 'permissions_' . (
			$with_parent_role_id ? 
				'with_parent_role_id' : 
				'without_parent_role_id'
		);

		// if we have cached permissions in ->_['permissions']
		if(isset($this->_[$cache_variable_name])) {
			return $this->_[$cache_variable_name];
		}

		// directly assigned permissions
		$directly_assigned_permissions = AccountsPermissions::_select([
				'AccountsPermissions.*'
			])
			->join(
				'AccountsPermissionsAssigned',
				'AccountsPermissionsAssigned.id_permission',
				'AccountsPermissions.id'
			)
			->where(['AccountsPermissionsAssigned.id_account'=>$this->get('id')])
			->execute();

		// inherited permissions
		$inherited_permissions = AccountsPermissions::_select(
			// depending if we want to keep permissions' parent-role origin
			$with_parent_role_id ? 
				[ 'AccountsPermissions.*', 'AccountsRolesAssigned.id_role' ] : 
				[ 'AccountsPermissions.*' ]
			)
			// get permissions that are actually assigned
			->join(
				'AccountsPermissionsAssigned',
				'AccountsPermissions.id',
				'AccountsPermissionsAssigned.id_permission'
			)
			// get permissions that are assigned to a role
			->join(
				'AccountsRolesAssigned',
				'AccountsRolesAssigned.id_role',
				'AccountsPermissionsAssigned.id_role'
			)
			// restrict to roles that are assigned to that account
			->where(['AccountsRolesAssigned.id_account'=>$this->get('id')])
			->execute();

		// deduplicate
		$permissions = array_unique(array_merge(
			$directly_assigned_permissions, 
			$inherited_permissions
		));

		// cache the permissions for later
		$this->_[$cache_variable_name] = $permissions;
		// return those
		return $permissions;
	}

	public function hasPermission(
		string|int|AccountsPermissions $mixed_permission
	) :bool {
		
		$permission = AccountsPermissions::getFromMixed(
			$mixed_permission
		);

		return in_array(
			$permission,
			$this->getPermissions()
		);

	}

	public function removePermissions(array $permissions) :void {
		// for each permission to remove
		foreach($permissions as $mixed_permission) {
			// remove the assignement
			$this->removePermission($mixed_permission);
		}
	}

	// PHP 8 AccountsPermissions|int|string
	public function removePermission(
		string|int|AccountsPermissions $mixed_permission
	) :bool {
		// remove permission 
		return (new AccountsPermissionsAssigned([
			'id_account'=>$this->get('id'),
			'id_permission'	=>AccountsPermissions::getFromMixed($mixed_permission)->get('id')
		]))->delete();
	}

	public function addPermissions(array $permissions) :void {
		// for each of the roles to add
		foreach($permissions as $mixed_permission) {
			// create a new assignement
			$this->addPermission($mixed_permission);
		}
	}

	// PHP 8 AccountsPermissions|int|string
	public function addPermission(
		string|int|AccountsPermissions $mixed_permission
	) :bool {
		// create a new permission assignment (rely on SQL constraints to prevent duplicates)
		return (new AccountsPermissionsAssigned)
			->set([
				'id_account'	=>$this->get('id'),
				'id_permission'	=>AccountsPermissions::getFromMixed($mixed_permission)->get('id')
			])
			->save();
	}


	public function setPermissions(array $permissions = []) :void {
		// remove all existing relations
		AccountsPermissionsAssigned::_delete()
			->where(['id_account'=>$this->get('id')])
			->execute();
		// insert in relation table
		$this->addPermissions($permissions);
	}


	public function removeRoles(array $roles) :void {
		// for each role to remove
		foreach($roles as $mixed_role) {
			// remove the assignment
			$this->removeRole($mixed_role);
		}
	}

	// PHP 8 AccountsRoles|int|string $mixed_role
	public function removeRole(
		string|int|AccountsRoles $mixed_role
	) :bool {
		// remove role 
		return (new AccountsRolesAssigned([
			'id_account'=>$this->get('id'),
			'id_role'	=>AccountsRoles::getFromMixed($mixed_role)->get('id')
		]))->delete();
	}

	public function addRoles(array $roles) :void {
		// for each of the roles to add
		foreach($roles as $mixed_role) {
			// create a new role assignment (rely on SQL constraints to prevent duplicates)
			$this->addRole($mixed_role);
		}
	}

	// PHP 8 AccountsRoles|int|string $mixed_role
	public function addRole(
		string|int|AccountsRoles $mixed_role
	) :bool {
		// create a new role assignment (rely on SQL constraints to prevent duplicates)
		return (new AccountsRolesAssigned)
			->set([
				'id_account'	=>$this->get('id'),
				'id_role'		=>AccountsRoles::getFromMixed($mixed_role)->get('id')
			])
			->save();

	}

	public function setRoles(array $roles = []) :void {
		// remove all existing relations
		AccountsRolesAssigned::_delete()
			->where(['id_account'=>$this->get('id')])
			->execute();
		// insert in relation table
		$this->addRoles($roles);
	}

}

?>