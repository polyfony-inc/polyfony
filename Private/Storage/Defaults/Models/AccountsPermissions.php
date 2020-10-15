<?php 

namespace Models;

use \Polyfony\{ Element, Locales };

class AccountsPermissions extends \Polyfony\Security\AccountsPermissions {
	
	const FILTERS = [
		'name'=>['length64','trim','ucfirst','capslock30']
	];

	public function getBadge() :Element {

		$title = Locales::get('Directly assigned permission');

		if($this->get('id_role')) {
			$role = $this->getRole();
			$title = Locales::get('Inherited from') . ' ';
			$title .= $role->getName();
		}

		return new Element('span', [
			'class'	=>'badge badge-pill badge-light',
			'title'	=>$title,
			'html'	=>
			($this->get('id_role') ? $this->getRole()->getDot() : ' ' ).
			Locales::get(
				$this->get('name', true)
			)
		]);

	}

}

?>
