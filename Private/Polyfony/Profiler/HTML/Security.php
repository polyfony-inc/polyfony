<?php

namespace Polyfony\Profiler\HTML;
use Bootstrap\Dropdown as Dropdown;
use Polyfony\Element as Element;

class Security {

	public static function getBody(
		Dropdown $security_dropdown
	) : Dropdown {

		$account = \Polyfony\Security::isAuthenticated() ? 
			\Polyfony\Security::getAccount() : null;

		$login = new Element('code', [
			'text'=>$account ? $account->get('login', true) : 'n/a'
		]);
		$security_dropdown->addItem([
			'html'=>"<strong>Login</strong> {$login}"
		]);

		$id = new Element('code', [
			'text'=>$account ? $account->get('id', true) : 'n/a'
		]);

		$security_dropdown->addItem([
			'html'=>"<strong>ID</strong> {$id}"
		]);

		$security_dropdown->addDivider();
		$security_dropdown->addHeader(['text'=>'Roles']);

		if($account) {
		
			foreach(
				$account->getRoles() as 
				$role
			) {
				$security_dropdown->addItem([
					'html'=>$role->getBadge()
				]);
			}

		}

		$security_dropdown->addDivider();
		$security_dropdown->addHeader(['text'=>'Permissions']);

	

		if($account) {
			foreach(
				$account->getPermissions() as 
				$permission
			) {
				
				$security_dropdown->addItem([
					'html'=>$permission->getBadge()
				]);
			}
		}

		

		return $security_dropdown;

	}

	public static function getComponent() : Dropdown {

		$account = \Polyfony\Security::isAuthenticated() ? 
			\Polyfony\Security::getAccount() : null;

		// SECURITY
		$security_dropdown = (new Dropdown)
			->setTrigger([
				'text'	=>' ' . ($account ? $account->get('login') : 'n/a'),
				'class'	=>'btn btn-security' . (\Polyfony\Config::get('profiler','use_small_buttons') ? ' btn-sm' : ''),
				'style'	=>'margin-left:10px'
			], 'fa fa-user-circle');
		

		$security_dropdown = self::getBody($security_dropdown);

		// restore: last login date
		// restore: session expiration date

		return $security_dropdown;

	}

}

?>
